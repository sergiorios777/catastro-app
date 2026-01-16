<?php

namespace App\Services;

use App\Models\Persona;
use App\Models\AnioFiscal;
use App\Models\DeterminacionPredial;
use App\Models\PropietarioPredio;
use Carbon\Carbon;

class CalculoImpuestoService
{
    protected Persona $persona;
    protected int $anio;
    protected AnioFiscal $anioFiscal;
    protected Carbon $fechaCalculo;

    public function __construct(Persona $persona, int $anio)
    {
        $this->persona = $persona;
        $this->anio = $anio;
        // Definimos la fecha de corte para validar la vigencia del beneficio (1 de Enero del año fiscal)
        $this->fechaCalculo = Carbon::create($anio, 1, 1);
        $this->anioFiscal = AnioFiscal::where('anio', $anio)->firstOrFail();
    }

    public function generarDeterminacion(array $sobrescrituras = []): DeterminacionPredial
    {
        // ---------------------------------------------------------
        // PREPARACIÓN DE DATOS
        // ---------------------------------------------------------

        // Cargar predios y Eager Loading de Beneficios
        $relaciones = PropietarioPredio::with(['predioFisico.beneficios.reglaDescuentoTributo'])
            ->where('persona_id', $this->persona->id)
            ->where('tenant_id', $this->persona->tenant_id)
            ->where('vigente', true)
            ->get();

        $sumaAutoavaluos = 0;
        $sumaValorComputable = 0;
        $sumaDescuentosPredios = 0;
        $conteoPredios = 0;
        $detallePredios = []; // Guardará el resultado matemático por predio

        // ---------------------------------------------------------
        // PASO 1: CÁLCULO INDIVIDUAL Y BENEFICIOS AL PREDIO
        // ---------------------------------------------------------
        foreach ($relaciones as $relacion) {
            $predio = $relacion->predioFisico;

            // Simulación de datos (si aplica)
            if (isset($sobrescrituras[$predio->id])) {
                $predio->fill($sobrescrituras[$predio->id]);
            }

            // A. Calcular valor físico (Ladrillo y cemento)
            $servicioPredio = new CalculoAutoavaluoService($predio, $this->anio);
            $totales = $servicioPredio->calcularTotal();
            $valorTotalPredio = $totales['total_autoavaluo'];

            // B. Valor base según porcentaje de propiedad
            $valorFiscal = $valorTotalPredio * ($relacion->porcentaje_propiedad / 100);

            // C. Lógica de Descuentos al Predio (Inafectaciones / Exoneraciones)
            // ------------------------------------------------------------------

            // 1. Filtrar beneficios vigentes
            $beneficiosVigentes = $predio->beneficios
                ->filter(function ($b) {
                    return $b->is_active
                        && $b->valid_from <= $this->fechaCalculo
                        && ($b->valid_to === null || $b->valid_to >= $this->fechaCalculo);
                });

            // 2. Variables de control
            $sumaDescuentos = 0;
            // $valorComputable = $valorFiscal;
            $beneficiosAplicadosLog = []; // Aquí guardamos qué descuentos se aplicaron

            // 3. Aplicación en base original
            foreach ($beneficiosVigentes as $beneficio) {
                $regla = $beneficio->reglaDescuentoTributo;

                // Solo aplicamos reglas de tipo predial (exoneracion/inafectacion)
                if (in_array($regla->tipo_beneficio, ['inafectacion', 'exoneracion'])) {

                    $porcentaje = $regla->porcentaje_descuento ?? 0;
                    $descuento = $valorFiscal * ($porcentaje / 100);
                    $sumaDescuentos += $descuento;

                    // Guardamos evidencia
                    $beneficiosAplicadosLog[] = [
                        'nombre' => $regla->nombre,
                        'porcentaje' => $porcentaje,
                        'documento' => $beneficio->documento_resolucion,
                        'monto_descontado' => $descuento,
                        'base_legal' => $regla->base_legal // Guardamos esto para el reporte
                    ];

                    /*if ($valorComputable <= 0)
                        break;*/
                }
            }

            // Restamos del valor computable
            $valorComputable = max(0, $valorFiscal - $sumaDescuentos);
            $sumaValorComputable += $valorComputable;

            $sumaAutoavaluos += $valorFiscal;
            $sumaDescuentosPredios += $sumaDescuentos;
            $conteoPredios++;

            // D. Guardamos metadata completa del predio para el snapshot
            $detallePredios[$predio->id] = [
                'cuc_referencia' => $predio->cuc ?? $predio->codigo_referencia ?? 'ID:' . $predio->id, // Para referencia rápida
                'valor_fisico' => $valorTotalPredio,
                'porcentaje_propiedad' => $relacion->porcentaje_propiedad,
                'valor_fiscal' => $valorFiscal,
                'valor_descuentos' => $sumaDescuentos,
                'valor_computable' => $valorComputable,
                'beneficios_log' => $beneficiosAplicadosLog // <--- Array de beneficios aplicados
            ];
        }

        // ---------------------------------------------------------
        // PASO 2: BENEFICIOS A LA PERSONA (Deducción 50 UIT)
        // ---------------------------------------------------------

        $baseImponibleEnProceso = $sumaValorComputable;
        $baseImponibleLegal = $sumaAutoavaluos; // Art. 11: Suma total de predios (ya neteados de inafectaciones)

        // Cargamos beneficios de la persona
        $this->persona->load('beneficios.reglaDescuentoTributo');

        $beneficiosPersona = $this->persona->beneficios
            ->filter(function ($b) {
                return $b->is_active
                    && $b->valid_from <= $this->fechaCalculo
                    && ($b->valid_to === null || $b->valid_to >= $this->fechaCalculo);
            });

        $totalDeduccionUIT = 0;

        // Array temporal para guardar los objetos beneficio de persona y usarlos en auditoría luego
        $beneficiosPersonaAplicados = [];

        foreach ($beneficiosPersona as $beneficio) {
            $regla = $beneficio->reglaDescuentoTributo;

            if ($regla->tipo_beneficio === 'deduccion' && $regla->aplicacion_sobre === 'base_imponible') {
                $totalDeduccionUIT += ($regla->valor_uit_deducidos ?? 0);
                $beneficiosPersonaAplicados[] = $beneficio; // Guardamos para el log
            }
        }

        // Calculamos montos
        $montoDeduccion = $totalDeduccionUIT * $this->anioFiscal->valor_uit;
        $baseAfecta = max(0, $baseImponibleEnProceso - $montoDeduccion);

        $sumaTotalDescuentos = $sumaDescuentosPredios + $montoDeduccion;

        // ---------------------------------------------------------
        // PASO 3: CÁLCULO DEL IMPUESTO
        // ---------------------------------------------------------

        $impuestoTotal = 0;
        $tramosSnapshot = [];

        if ($baseAfecta > 0) {
            $resultado = $this->calcularDesgloseImpuesto($baseAfecta, (float) $this->anioFiscal->valor_uit);
            $tramosSnapshot = $resultado['tramos'];
            $impuestoTotal = $resultado['total_impuesto'];
        }

        // Impuesto Mínimo
        $impuestoMinimo = $this->anioFiscal->tasa_minima_predial ?? ($this->anioFiscal->valor_uit * 0.006);

        // Nota: No sobrescribimos $impuestoCalculado aquí si es menor al mínimo, 
        // lo manejamos al guardar en BD para no falsear la matemática de los tramos.
        // Pero para efectos de cobro final, usaremos el mayor.
        $impuestoFinalCobro = ($baseAfecta > 0 && $impuestoTotal < $impuestoMinimo)
            ? $impuestoMinimo
            : $impuestoTotal;

        /*if ($baseAfecta > 0 && $impuestoTotal < $impuestoMinimo) {
            $impuestoTotal = $impuestoMinimo;
        }*/

        // --- EXONERACIONES AL IMPUESTO FINAL (Si hubiera) ---
        // (Lógica omitida por brevedad, pero iría aquí si se requiriera)

        // ---------------------------------------------------------
        // PASO 4: GENERACIÓN DE AUDITORÍA Y SNAPSHOT
        // ---------------------------------------------------------

        // Aquí construimos el array "$auditBeneficios" para el PDF HR
        $detallesBeneficios = [];

        // A. Agregar beneficios de Persona (Deducciones)
        foreach ($beneficiosPersonaAplicados as $beneficio) {
            $regla = $beneficio->reglaDescuentoTributo;
            $detallesBeneficios[] = [
                'origen' => 'persona',
                'concepto' => $regla->nombre,
                'tipo' => 'Deducción Base',
                'base_legal' => $regla->base_legal,
                'documento' => $beneficio->documento_resolucion,
                'fecha_inicio' => $beneficio->valid_from->format('d/m/Y'),
                'monto_efecto' => $montoDeduccion, // El monto total deducido
                'referencia' => 'Global'
            ];
        }

        // B. Agregar beneficios de Predios (Exoneraciones)
        // Leemos lo que calculamos en el Paso 1
        foreach ($detallePredios as $predioId => $meta) {
            if (!empty($meta['beneficios_log'])) {
                foreach ($meta['beneficios_log'] as $log) {
                    $detallesBeneficios[] = [
                        'origen' => 'predio',
                        'concepto' => $log['nombre'],
                        'tipo' => 'Exoneración',
                        'base_legal' => $log['base_legal'] ?? '-',
                        'documento' => $log['documento'],
                        'fecha_inicio' => '-',
                        'monto_efecto' => $log['monto_descontado'],
                        'referencia' => $meta['cuc_referencia']
                    ];
                }
            }
        }

        // C. Estructura Final del Snapshot
        $datosParaSnapshot = [
            'predios' => PropietarioPredio::with([
                'predioFisico.construcciones',
                'predioFisico.obrasComplementarias',
                'predioFisico.tenant',
                'predioFisico.beneficios' => function ($q) {
                    $q->where('is_active', true);
                }
            ])
                ->where('persona_id', $this->persona->id)
                ->where('tenant_id', $this->persona->tenant_id)
                ->where('vigente', true)
                ->get()
                ->toArray(),

            'calculos_internos' => $detallePredios,
            'auditoria_beneficios' => $detallesBeneficios,  // IMPORTANTE: Esta es la clave que lee tu HR Blade
            'tramos' => $tramosSnapshot,    // IMPORTANTE: Esta es la clave que lee tu LP Blade
            'resumen_economico' => [
                'total_autoavaluo_bruto' => $baseImponibleLegal,
                'total_descuentos_predios' => $sumaDescuentosPredios,
                'base_afecta_sin_deducciones' => $baseImponibleLegal - $sumaDescuentosPredios,
                'total_deduccion_base' => $montoDeduccion,
                'total_descuentos' => $sumaTotalDescuentos,
                'base_imponible_afecta' => $baseAfecta,
            ],
        ];

        return DeterminacionPredial::updateOrCreate(
            [
                'tenant_id' => $this->persona->tenant_id,
                'persona_id' => $this->persona->id,
                'anio_fiscal_id' => $this->anioFiscal->id,
            ],
            [
                'cantidad_predios' => $conteoPredios,
                'base_imponible' => $baseImponibleLegal, // Art. 11
                'valor_uit' => $this->anioFiscal->valor_uit,
                'impuesto_calculado' => $impuestoTotal,
                'impuesto_final_cobro' => $impuestoFinalCobro,
                'tasa_minima' => $impuestoMinimo,
                'fecha_emision' => now(),
                'snapshot_datos' => $datosParaSnapshot,
            ]
        );
    }

    /**
     * Calcula el impuesto y devuelve tanto el TOTAL como el DETALLE por tramos.
     * Reemplaza al antiguo calcularImpuestoEscalonado.
     * * @param float $baseCalculo (Es la Base Afecta)
     * @param float $uit
     * @return array ['total_impuesto' => float, 'tramos' => array]
     */
    private function calcularDesgloseImpuesto(float $baseCalculo, float $uit): array
    {
        // Validación de seguridad
        if ($baseCalculo <= 0) {
            return ['total_impuesto' => 0.0, 'tramos' => []];
        }

        $detalleTramos = [];
        $impuestoTotal = 0.0;
        $remanente = $baseCalculo;

        // Configuración de Tramos (Hardcoded o desde DB)
        $configuracionTramos = [
            [
                'limite_uit' => 15,
                'tasa' => 0.002, // 0.2%
                'etiqueta' => 'Hasta 15 UIT'
            ],
            [
                'limite_uit' => 60, // Límite ACUMULADO (hasta 60 UIT en total)
                'tasa' => 0.006, // 0.6%
                'etiqueta' => 'Más de 15 a 60 UIT'
            ],
            [
                'limite_uit' => INF,
                'tasa' => 0.010, // 1.0%
                'etiqueta' => 'Más de 60 UIT'
            ]
        ];

        $uitAcumuladasPrevias = 0;

        foreach ($configuracionTramos as $cfg) {
            if ($remanente <= 0)
                break;

            // 1. Definir el techo de este tramo en UITs relativas
            // Tramo 1: 15 - 0 = 15
            // Tramo 2: 60 - 15 = 45
            // Tramo 3: INF
            $anchoTramoUit = $cfg['limite_uit'] - $uitAcumuladasPrevias;

            // 2. Convertir a Soles
            $topeSoles = ($cfg['limite_uit'] === INF) ? INF : ($anchoTramoUit * $uit);

            // 3. ¿Cuánto dinero cae en este cubo?
            $montoEnTramo = ($remanente > $topeSoles) ? $topeSoles : $remanente;

            // 4. Calcular impuesto
            // IMPORTANTE: Redondear aquí para coincidir con la visualización línea a línea
            $impuestoTramo = round($montoEnTramo * $cfg['tasa'], 2);

            // 5. Guardar detalle
            $detalleTramos[] = [
                'etiqueta' => $cfg['etiqueta'],
                'base_tramo' => round($montoEnTramo, 2), // Base imponible de este pedacito
                'alicuota' => ($cfg['tasa'] * 100), // Ej. 0.2
                'impuesto_tramo' => $impuestoTramo
            ];

            // 6. Actualizar contadores
            $impuestoTotal += $impuestoTramo;
            $remanente -= $montoEnTramo;

            // Guardamos el límite actual para usarlo como piso del siguiente
            $uitAcumuladasPrevias = $cfg['limite_uit'];
        }

        return [
            'total_impuesto' => round($impuestoTotal, 2), // La suma de los redondeos parciales
            'tramos' => $detalleTramos
        ];
    }

    /*
    protected function calcularImpuestoEscalonado(float $baseImponible, float $uit): float
    {
        $impuesto = 0;
        $montoRestante = $baseImponible;

        // TRAMO 1: Hasta 15 UIT (0.2%)
        $tramo1 = 15 * $uit;

        if ($montoRestante > $tramo1) {
            $impuesto += round($tramo1 * 0.002, 2);
            $montoRestante -= $tramo1;
        } else {
            $impuesto += round($montoRestante * 0.002, 2);
            return $impuesto;
        }

        // TRAMO 2: Exceso de 15 hasta 60 UIT (0.6%)
        $tramo2 = 45 * $uit;

        if ($montoRestante > $tramo2) {
            $impuesto += round($tramo2 * 0.006, 2);
            $montoRestante -= $tramo2;
        } else {
            $impuesto += round($montoRestante * 0.006, 2);
            return $impuesto;
        }

        // TRAMO 3: Exceso de 60 UIT (1.0%)
        $impuesto += round($montoRestante * 0.010, 2);

        return $impuesto;
    }
    */
}
