<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>HR - Hoja de Resumen</title>
    <style>
        body {
            font-family: sans-serif;
            font-size: 11px;
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
        }

        .header h1 {
            margin: 0;
            font-size: 18px;
            font-weight: bold;
        }

        .header p {
            margin: 2px;
        }

        .box {
            border: 1px solid #000;
            padding: 5px;
            margin-bottom: 10px;
        }

        .box-title {
            background: #eee;
            font-weight: bold;
            padding: 2px 5px;
            border-bottom: 1px solid #000;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }

        th,
        td {
            border: 1px solid #000;
            padding: 4px;
            text-align: left;
        }

        th {
            background: #f0f0f0;
            font-size: 10px;
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .bold {
            font-weight: bold;
        }

        .footer-firmas {
            margin-top: 50px;
        }

        .firma-box {
            float: left;
            width: 45%;
            border-top: 1px solid #000;
            text-align: center;
            padding-top: 5px;
            margin-right: 5%;
        }
    </style>
</head>

<body>

    <div class="header">
        <h1>HOJA DE RESUMEN (HR)</h1>
        <p>DECLARACIÓN JURADA DEL IMPUESTO PREDIAL - AÑO {{ $anio }}</p>
        <p>MUNICIPALIDAD DE {{ strtoupper($municipio->distrito ?? 'ROSA PANDURO') }}</p>
    </div>

    <div class="box">
        <div class="box-title">I. DATOS DEL CONTRIBUYENTE</div>
        <table style="border: none; margin: 0;">
            <tr style="border: none;">
                <td style="border: none; width: 15%;"><strong>Código:</strong> {{ $determinacion->persona->id }}</td>
                <td style="border: none; width: 50%;"><strong>Nombre:</strong> {{ $determinacion->persona->apellidos }}
                    {{ $determinacion->persona->nombres }} {{ $determinacion->persona->razon_social }}
                </td>
                <td style="border: none;"><strong>Doc:</strong> {{ $determinacion->persona->numero_documento }}</td>
            </tr>
            <tr style="border: none;">
                <td style="border: none;" colspan="3"><strong>Domicilio Fiscal:</strong>
                    {{ $determinacion->persona->direccion }}</td>
            </tr>
            <tr style="border: none;">
                <td style="border: none; padding: 0;" colspan="3">
                    <table style="border: none; margin: 0;">
                        <tr style="border: none;">
                            <td style="border: none;"><strong>Departamento:</strong> {{ $determinacion->persona->ubicacion_geografica['departamento'] }}</td>
                            <td style="border: none;"><strong>Provincia:</strong> {{ $determinacion->persona->ubicacion_geografica['provincia'] }}</td>
                            <td style="border: none;"><strong>Distrito:</strong> {{ $determinacion->persona->ubicacion_geografica['distrito'] }}</td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    </div>

    <div class="box-title">II. RELACIÓN DE PREDIOS</div>
    <table>
        <thead>
            <tr>
                <th width="5%">#</th>
                <th width="10%">Tipo</th>
                <th width="50%">Cód. Predio / Ubicación del Predio</th>
                <th width="10%">% Prop.</th>
                <th width="15%">Autoavalúo (S/.)</th>
            </tr>
        </thead>
        <tbody>
            @php $i = 1; @endphp
            @foreach($predios as $relacion)
                <tr>
                    <td class="text-center">{{ $i++ }}</td>
                    <td>{{ $relacion->predioFisico->tipo_predio ?? '---' }}</td>
                    <td>{{ $relacion->predioFisico->cuc ?? 'S/N' }} - {{ $relacion->predioFisico->direccion }} -
                        {{ $relacion->predioFisico->sector }}
                    </td>
                    <td class="text-center">{{ $relacion->porcentaje_propiedad }}%</td>
                    <td class="text-right">S/.
                        {{ number_format($calculos[$relacion->predioFisico->id]['valor_fiscal'] ?? 0, 2) }}

                        @php
                            $esCondominio = ($relacion->porcentaje_propiedad < 100);
                        @endphp

                        @if($esCondominio)
                            <br>
                            <span style="font-size: 8px; color: #666">
                                (Total predio: S/
                                {{ number_format($calculos[$relacion->predioFisico->id]['valor_fisico'] ?? 0, 2) }})
                            </span>
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    {{-- INICIO: NUEVA SECCIÓN DE BENEFICIOS --}}
    @php
        $snapshot = is_array($determinacion->snapshot_datos)
            ? $determinacion->snapshot_datos
            : json_decode(json_encode($determinacion->snapshot_datos), true);

        $auditBeneficios = $snapshot['auditoria_beneficios'] ?? [];
        $resumen = $snapshot['resumen_economico'] ?? [
            'total_autoavaluo_bruto' => $determinacion->base_imponible, // Fallback
            'total_deduccion_base' => 0,
            'base_imponible_afecta' => $determinacion->base_imponible,
            'total_descuentos' => 0
        ];
    @endphp

    {{-- SECCIÓN III: BENEFICIOS TRIBUTARIOS APROBADOS --}}
    @if(count($auditBeneficios) > 0)
        <div class="box">
            <div class="box-title">III. DETALLE DE BENEFICIOS Y DEDUCCIONES APLICADAS</div>
            <table style="width: 100%; border-collapse: collapse; font-size: 9px;">
                <thead>
                    <tr style="background-color: #f0f0f0;">
                        <th width="15%">Beneficio / Norma</th>
                        <th width="20%">Base Legal</th>
                        <th width="15%">Documento Aprob.</th>
                        <th width="10%">Vigencia</th>
                        <th width="15%">Aplicado A</th>
                        <th width="10%">Tipo</th>
                        <th width="15%">Monto Afectado (S/.)</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($auditBeneficios as $item)
                        <tr>
                            <td>{{ $item['concepto'] }}</td>
                            <td>{{ $item['base_legal'] }}</td>
                            <td class="text-center">{{ $item['documento'] }}</td>
                            <td class="text-center">{{ $item['fecha_inicio'] }}</td>
                            <td class="text-center">
                                {{ $item['origen'] == 'persona' ? 'GLOBAL' : 'Predio: ' . $item['referencia'] }}
                            </td>
                            <td class="text-center">{{ $item['tipo'] }}</td>
                            <td class="text-right">{{ number_format($item['monto_efecto'], 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <div style="font-size: 9px; margin-top: 2px; font-style: italic;">
                * El "Monto Afectado" representa la deducción sobre la base imponible o el valor no gravado del predio.
            </div>
        </div>
    @endif

    <div class="box">
        <div class="box-title">IV. LIQUIDACIÓN DEL IMPUESTO PREDIAL</div>
        <table style="width: 50%; float: right; margin-top: 10px;">
            <tr>
                <td style="border:none; text-align: right; padding-right: 10px;">
                    <strong>Total Predios Declarados:</strong></td>
                <td class="text-center">{{ $determinacion->cantidad_predios }}</td>
            </tr>
            {{-- 1. Base Imponible Legal (Art. 11) --}}
            <tr>
                <td style="border:none; text-align: right; padding-right: 10px;">
                    <strong>Base Imponible (Total Autoavalúo):</strong>
                    <br><span style="font-size: 8px; color: #666;">(Art. 11 TUO Ley Tributación Municipal)</span>
                </td>
                <td style="width: 100px; text-align: right; background-color: #f9f9f9; vertical-align: top;">
                    {{ number_format($resumen['total_autoavaluo_bruto'], 2) }}
                </td>
            </tr>

            {{-- 2. Deducción (Art. 19) --}}
            @if($resumen['total_descuentos'] > 0)
                <tr>
                    <td style="border:none; text-align: right; padding-right: 10px; color: #d9534f;">
                        (-) Deducción por Beneficio
                        ({{ intval($resumen['total_deduccion_base'] / $determinacion->valor_uit) }} UIT):
                        <br><span style="font-size: 8px; color: #666;">(Art. 19 TUO - Pensionista / Adulto Mayor)</span>
                    </td>
                    <td style="text-align: right; color: #d9534f; vertical-align: top;">
                        ( {{ number_format($resumen['total_deduccion_base'], 2) }} )
                    </td>
                </tr>
                <tr>
                    <td style="border:none; text-align: right; padding-right: 10px; color: #d9534f;">
                        (-) Otros Beneficios:
                        <br><span style="font-size: 8px; color: #666;">(Art. 17 TUO D.S. 156-2004-EF; Otros)</span>
                    </td>
                    <td style="text-align: right; color: #d9534f; vertical-align: top;">
                        ( {{ number_format($resumen['total_descuentos'] - $resumen['total_deduccion_base'], 2) }} )
                    </td>
                </tr>

                {{-- 3. Base Afecta (Resultado) --}}
                <tr>
                    <td
                        style="border:none; text-align: right; padding-right: 10px; font-weight: bold; border-top: 1px dashed #ccc;">
                        (=) Base Imponible Afecta:
                    </td>
                    <td style="text-align: right; font-weight: bold; border-top: 1px dashed #ccc;">
                        {{ number_format($resumen['base_imponible_afecta'], 2) }}
                    </td>
                </tr>
            @endif

            {{-- Espaciador --}}
            <tr>
                <td colspan="2" style="border:none; height: 5px;"></td>
            </tr>

            {{-- 4. Impuesto --}}
            <tr>
                <td style="border:none; text-align: right; padding-right: 10px;">Impuesto Calculado (Anual):</td>
                <td style="text-align: right;">
                    {{ number_format($determinacion->impuesto_calculado, 2) }}
                </td>
            </tr>

            {{-- 5. Monto Mínimo a Pagar --}}
            {{-- <tr>
                <td style="border:none; text-align: right; padding-right: 10px;">Monto Mínimo a Pagar:</td>
                <td style="text-align: right;">
                    {{ number_format($determinacion->tasa_minima, 2) }}
                </td>
            </tr> --}}
        </table>
        <div style="clear: both;"></div>
    </div>

    <div class="footer-firmas">
        <div class="firma-box">
            Firma del Contribuyente<br>
            DNI: __________________
        </div>
        <div class="firma-box">
            Sello y Firma de Recepción<br>
            Municipalidad
        </div>
    </div>

    <div style="margin-top: 50px; font-size: 9px; text-align: center;">
        Fecha de Emisión: {{ \Carbon\Carbon::parse($determinacion->fecha_emision)->format('d/m/Y H:i') }} | Usuario:
        {{ $user_name }}
    </div>

</body>

</html>