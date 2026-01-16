<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>HR - Hoja Resumen</title>
    <style>
        @page {
            margin: 1cm;
            font-family: Arial, Helvetica, sans-serif;
        }

        body {
            font-size: 9px;
            /* Un poco más pequeño que LP para que quepan las columnas */
            color: #000;
        }

        .w-100 {
            width: 100%;
        }

        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        .text-left {
            text-align: left;
        }

        .font-bold {
            font-weight: bold;
        }

        .uppercase {
            text-transform: uppercase;
        }

        /* Tablas */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 4px;
        }

        td,
        th {
            padding: 3px;
            vertical-align: top;
        }

        /* Bordes */
        .bordered-table td,
        .bordered-table th {
            border: 1px solid #000;
        }

        .no-border {
            border: none !important;
        }

        .top-border {
            border-top: 1px solid #000;
        }

        /* Cajas HR / LP */
        .box-hr {
            border: 2px solid #000;
            padding: 5px;
            width: 100px;
            float: right;
        }

        .hr-title {
            font-size: 30px;
            font-weight: bold;
            line-height: 1;
        }

        .hr-subtitle {
            font-size: 10px;
            font-weight: bold;
        }

        .hr-muni {
            font-size: 8px;
            text-transform: uppercase;
        }

        /* Secciones */
        .section-header {
            background-color: #e0e0e0;
            font-weight: bold;
            border: 1px solid #000;
            padding: 2px 5px;
            margin-top: 8px;
            font-size: 10px;
        }

        .legal-text {
            font-size: 8px;
            text-align: justify;
            margin-top: 15px;
            line-height: 1.2;
            font-style: italic;
        }
    </style>
</head>

<body>

    <table class="w-100 no-border">
        <tr>
            <td width="20%" class="text-center" style="vertical-align: middle;">
                @if(!empty($municipio_logo))
                    <img src="{{ $municipio_logo }}" alt="Logo" style="height: 50px;">
                @else
                    <div style="height: 50px; width: 50px; background: #eee; margin: 0 auto;"></div>
                @endif
                <br>
                <span class="hr-muni">{{ $municipio->name }}</span>
            </td>

            <td width="60%" class="text-center" style="vertical-align: middle;">
                <h2 style="margin: 0; font-size: 14px;">ACTUALIZACIÓN DE VALORES DEL IMPUESTO PREDIAL DEL {{ $anio }}
                </h2>
                <div style="font-size: 9px; margin-top: 5px;">
                    T.U.O. DE LA LEY DE TRIBUTACIÓN MUNICIPAL<br>
                    (D.S. Nº 156-2004-EF y modificatorias)
                </div>
            </td>

            <td width="20%" align="right">
                <div class="box-hr text-center">
                    <div class="hr-title">HR</div>
                    <div class="hr-subtitle">HOJA RESUMEN</div>
                </div>
            </td>
        </tr>
    </table>

    <table class="w-100" style="margin-top: 5px; border-bottom: 1px solid #000;">
        <tr>
            <td class="text-left font-bold">
                CÓDIGO DEL CONTRIBUYENTE: {{ $persona->numero_documento }}
            </td>
            <td class="text-right font-bold">
                FECHA DE EMISIÓN: {{ now()->format('d/m/Y') }}
            </td>
        </tr>
    </table>

    <div class="section-header">SECCIÓN I. DATOS DEL CONTRIBUYENTE</div>
    <table class="bordered-table w-100">
        <thead>
            <tr style="background-color: #f0f0f0;">
                <th class="text-left" width="70%">APELLIDOS Y NOMBRES / RAZÓN SOCIAL</th>
                <th class="text-left" width="30%">TIPO Y NÚMERO DE DOCUMENTO</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>{{ $persona->nombre_completo }}</td>
                <td>{{ $persona->tipo_documento }} - {{ $persona->numero_documento }}</td>
            </tr>
            <tr>
                <td style="height: 15px;">
                    <small style="color: #666;">(CÓNYUGE):</small>
                    {{ $persona->conyuge_nombre ?? '' }}
                </td>
                <td>
                    {{ $persona->conyuge_documento ?? '' }}
                </td>
            </tr>
        </tbody>
    </table>

    <div class="section-header">SECCIÓN II. DATOS DEL REPRESENTANTE LEGAL</div>
    <table class="bordered-table w-100">
        <thead>
            <tr style="background-color: #f0f0f0;">
                <th class="text-left" width="70%">APELLIDOS Y NOMBRES / RAZÓN SOCIAL</th>
                <th class="text-left" width="30%">TIPO Y NÚMERO DE DOCUMENTO</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td style="height: 15px;">
                    {{ $persona->representante_legal_nombre ?? '' }}
                </td>
                <td>
                    {{ $persona->representante_legal_documento ?? '' }}
                </td>
            </tr>
        </tbody>
    </table>

    <div class="section-header">SECCIÓN III. DOMICILIO FISCAL</div>
    <table class="bordered-table w-100">
        <tr>
            <td colspan="4">
                <strong>DIRECCIÓN:</strong> {{ $persona->direccion }} &nbsp;
                <strong>ZONA:</strong> {{ $persona->ubicacion_grografica['sector_zona'] ?? '-' }} &nbsp;
                <strong>MZ:</strong> {{ $persona->ubicacion_grografica['manzana'] ?? '-' }} &nbsp;
                <strong>LT:</strong> {{ $persona->ubicacion_grografica['lote'] ?? '-' }}
            </td>
        </tr>
        <tr>
            <td colspan="4">
                <strong>DENOMINACIÓN URBANA:</strong> {{ $persona->ubicacion_grografica['denominacion_urbana'] ?? '-' }}
                <br>
                <strong>REFERENCIAS:</strong> {{ $persona->ubicacion_grografica['referencia'] ?? '-' }}
            </td>
        </tr>
        <tr>
            <td width="20%"><strong>DPTO:</strong> {{ $persona->ubicacion_grografica['departamento'] ?? '' }}</td>
            <td width="20%"><strong>PROV:</strong> {{ $persona->ubicacion_grografica['provincia'] ?? '' }}</td>
            <td width="20%"><strong>DIST:</strong> {{ $persona->ubicacion_grografica['distrito'] ?? '' }}</td>
            <td width="40%"><strong>CUENCA/LOC:</strong> {{ $persona->ubicacion_grografica['cuenca'] ?? '-' }} /
                {{ $persona->ubicacion_grografica['localidad'] ?? '-' }}
            </td>
        </tr>
    </table>

    <div class="section-header">SECCIÓN IV. RELACIÓN DE PREDIOS</div>
    <table class="bordered-table w-100">
        <thead>
            <tr style="background-color: #f0f0f0; font-size: 8px;">
                <th width="5%">ITEM</th>
                <th width="10%">CÓDIGO / CUC</th>
                <th width="25%">UBICACIÓN DEL PREDIO</th>
                <th width="11%">VALOR TOTAL<br>PREDIO</th>
                <th width="6%">% PROP</th>
                <th width="11%">AUTOAVALÚO</th>
                <th width="11%">INFECTACIONES/<br>EXONERACIONES</th>
                <th width="11%">BASE AFECTA <br> (Predios)</th>
            </tr>
        </thead>
        <tbody>
            @php
                // Recuperamos datos
                $predios = $determinacion->snapshot_datos['predios'] ?? [];
                $calculos = $determinacion->snapshot_datos['calculos_internos'] ?? [];
                $resumen = $determinacion->snapshot_datos['resumen_economico'] ?? [];
                $item = 1;

                // Totales globales para la fila final
                $totalAutoavaluo = $determinacion->base_imponible;
                $totalExoneracion = $resumen['total_descuentos_predios'] ?? 0;
                $baseAfectaSinDeduccion = $resumen['base_afecta_sin_deducciones'] ?? 0;
                $totalDeduccion = $resumen['total_deduccion_base'] ?? 0;
                $baseAfectaTotal = $resumen['base_imponible_afecta'] ?? $totalAutoavaluo;
            @endphp

            @foreach($predios as $relacion)
                @php
                    $predioFisico = $relacion['predio_fisico'];
                    $predioId = $predioFisico['id'];
                    $meta = $calculos[$predioId] ?? [];

                    $valorTotal = $meta['valor_fisico'] ?? 0;
                    $porcentaje = $meta['porcentaje_propiedad'] ?? 100;
                    $valorParte = $meta['valor_fiscal'] ?? 0;
                    $valorExoneracion = $meta['valor_descuentos'] ?? 0;
                    $valorAfecto = $meta['valor_computable'] ?? 0;

                @endphp

                <tr>
                    <td class="text-center">{{ $item++ }}</td>
                    <td class="text-center">{{ $predioFisico['cuc'] ?? $predioFisico['codigo_referencia'] ?? '-' }}</td>
                    <td style="font-size: 8px;">
                        {{ $predioFisico['direccion'] ?? '' }}
                        {{ isset($predioFisico['nombre_predio']) ? ' - ' . $predioFisico['nombre_predio'] : '' }}
                    </td>
                    <td class="text-right">{{ number_format($valorTotal, 2) }}</td>
                    <td class="text-center">{{ number_format($porcentaje, 2) }}%</td>
                    <td class="text-right">{{ number_format($valorParte, 2) }}</td>
                    <td class="text-right">{{ number_format($valorExoneracion, 2) }}</td>
                    <td class="text-right">{{ number_format($valorAfecto, 2) }}</td>
                </tr>
            @endforeach

            @if(empty($predios))
                <tr>
                    <td colspan="8" class="text-center">NO REGISTRA PREDIOS</td>
                </tr>
            @endif
        </tbody>

        {{-- FILA DE RESUMEN / PIE DE TABLA --}}
        <tfoot>
            {{-- Fila 1: Total Autoavalúo --}}
            <tr style="font-weight: bold; background-color: #fafafa;">
                <td colspan="6" class="no-border"></td>
                <td colspan="1" class="text-right" style="border: 1px solid #000;">TOTAL</td>
                <td class="text-right" style="border: 1px solid #000;">{{ number_format($baseAfectaSinDeduccion, 2) }}
                </td>
            </tr>

            {{-- Fila 2: Deducciones (Solo si existen, para ahorrar espacio) --}}
            @if($totalDeduccion > 0)
                <tr>
                    <td colspan="6" class="no-border"></td>
                    <td colspan="1" class="text-right" style="font-size: 8px; color: #666; border: 1px solid #000;">(-)
                        DEDUCCIONES</td>
                    <td class="text-right" style="color: #666; border: 1px solid #000;">
                        {{ number_format($totalDeduccion, 2) }}
                    </td>
                </tr>

                {{-- Fila 3: Base Imponible Afecta (Resultado Final) --}}
                <tr style="font-weight: bold; background-color: #e0e0e0;">
                    <td colspan="6" class="no-border"></td>
                    <td colspan="1" class="text-right" style="border: 1px solid #000;">BASE AFECTA</td>
                    <td class="text-right" style="border: 1px solid #000; font-size: 11px;">
                        {{ number_format($baseAfectaTotal, 2) }}
                    </td>
                </tr>
            @endif
        </tfoot>
    </table>

    <table class="w-100 no-border" style="margin-top: 10px;">
        <tr>
            <td width="40%" style="padding-right: 20px;">
                <table class="bordered-table w-100">
                    <tr style="background-color: #f0f0f0;">
                        <th colspan="2">TOTAL DE PREDIOS</th>
                    </tr>
                    <tr>
                        <td width="70%">DECLARADOS</td>
                        <td width="30%" class="text-center font-bold">
                            {{ $determinacion->cantidad_predios }}
                        </td>
                    </tr>
                    <tr>
                        <td>AFECTOS</td>
                        <td class="text-center font-bold">
                            {{-- Ajustar lógica si tienes predios 100% inafectos --}}
                            {{ $determinacion->cantidad_predios }}
                        </td>
                    </tr>
                </table>

                <div
                    style="margin-top: 20px; border: 1px dashed #999; height: 40px; text-align: center; padding-top:10px; font-size: 8px; color: #666;">
                    (ESPACIO RESERVADO MUNICIPALIDAD)
                </div>
            </td>

            <td width="60%" style="padding-left: 20px;">
                <table class="bordered-table w-100">
                    <tr style="background-color: #f0f0f0;">
                        <th colspan="2">RESUMEN DE VALORES S/</th>
                    </tr>
                    <tr>
                        <td width="60%">TOTAL AUTOAVALÚO</td>
                        <td width="40%" class="text-right">
                            {{ number_format($resumen['total_autoavaluo_bruto'], 2) }}
                        </td>
                    </tr>
                    <tr>
                        <td>
                            BASE IMPONIBLE AFECTA
                        </td>
                        <td class="text-right font-bold">
                            {{ number_format($resumen['base_imponible_afecta'] ?? 0, 2) }}
                        </td>
                    </tr>

                    {{-- Espacio vacío para separar impuesto --}}
                    <tr>
                        <td colspan="2" class="no-border" style="height: 2px;"></td>
                    </tr>

                    <tr style="background-color: #f0f0f0;">
                        <td><strong>IMPUESTO ANUAL</strong></td>
                        <td class="text-right font-bold">
                            @php
                                if ($resumen['base_imponible_afecta'] > 0) {
                                    $final = max($determinacion->impuesto_calculado, $determinacion->tasa_minima);
                                } else {
                                    $final = 0;
                                }
                            @endphp
                            {{ number_format($final, 2) }}
                        </td>
                    </tr>
                    <tr>
                        <td>IMPUESTO TRIMESTRAL</td>
                        <td class="text-right">
                            {{ number_format($final / 4, 2) }}
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <div class="legal-text">
        <strong>IMPORTANTE:</strong><br>
        Si al vencimiento de la primera cuota no existe objeción, la presente tendrá efectos de Declaración Jurada para
        el presente ejercicio,
        cualquier modificación posterior afectará el siguiente ejercicio (Art. 14 T.U.O. del Código Tributario, D.S.
        N°156-2004-EF, artículo 88°).
        Asimismo, el contribuyente declara bajo juramento que los datos consignados en la presente declaración son
        verdaderos.
    </div>

</body>

</html>