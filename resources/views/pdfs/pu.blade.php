<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>PU - Predio Urbano</title>
    <style>
        body {
            font-family: sans-serif;
            font-size: 10px;
        }

        .header {
            text-align: center;
            margin-bottom: 15px;
        }

        .header h1 {
            margin: 0;
            font-size: 16px;
            font-weight: bold;
        }

        .box {
            border: 1px solid #000;
            margin-bottom: 5px;
        }

        .box-header {
            background: #ddd;
            padding: 2px 5px;
            font-weight: bold;
            border-bottom: 1px solid #000;
            font-size: 9px;
        }

        .box-content {
            padding: 5px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 9px;
        }

        th,
        td {
            border: 1px solid #666;
            padding: 3px;
        }

        th {
            background: #f2f2f2;
            text-align: center;
        }

        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        .font-bold {
            font-weight: bold;
        }

        .sin-borde {
            border: none !important;
        }
    </style>
</head>

<body>

    <div class="header">
        <h1>FORMATO 
            @if ($predio->tipo_predio == 'urbano')
                PU (PREDIO URBANO)
            @else
                PR (PREDIO RURAL)
            @endif
        </h1>
        <span>AÑO FISCAL {{ $anio }}</span><br>
        <span>{{ strtoupper($municipio->name) }}</span>
    </div>

    <div class="box">
        <div class="box-header">I. DATOS DEL CONTRIBUYENTE</div>
        <div class="box-content">
            @if($persona)
                <strong>Nombre/Razón Social:</strong> {{ $persona->apellidos }} {{ $persona->nombres }}
                {{ $persona->razon_social }} <br>
                <strong>Documento:</strong> {{ $persona->numero_documento }} <br>
                <strong>Dirección:</strong> {{ $persona->direccion }} ({{ $persona->ubicacion_geografica['cuenca'] ?? "" }}-{{ $persona->ubicacion_geografica['localidad'] ?? "" }} | {{ $persona->ubicacion_geografica['departamento'] ?? "" }}-{{ $persona->ubicacion_geografica['provincia'] ?? "" }}-{{ $persona->ubicacion_geografica['distrito'] ?? "" }})
            @else
                <em>Sin propietario vigente asignado</em>
            @endif
        </div>
    </div>

    <div class="box">
        <div class="box-header">II. UBICACIÓN DEL PREDIO</div>
        <div class="box-content">
            <table class="sin-borde">
                <tr class="sin-borde">
                    <td class="sin-borde" width="13%"><strong>Cód. Catastral:</strong></td>
                    <td class="sin-borde" width="37%">{{ $predio->cuc ?? '---' }}</td>
                    <td class="sin-borde" width="13%"><strong>Cód. Ref:</strong></td>
                    <td class="sin-borde">{{ $predio->codigo_referencia }}</td>
                    <td class="sin-borde" width="13%"><strong>Tipo de predio:</strong></td>
                    <td class="sin-borde">{{ strtoupper($predio->tipo_predio) }}</td>
                </tr>
                <tr class="sin-borde">
                    <td class="sin-borde"><strong>Dirección:</strong></td>
                    <td class="sin-borde" colspan="5">{{ $predio->direccion }} - {{ $predio->sector }}</td>
                </tr>
                <tr class="sin-borde">
                    <td class="sin-borde" colspan="6" style="padding: 0;">
                        <table class="sin-borde">
                            <tr class="sin-borde">
                                <td class="sin-borde" width="8%"><strong>Cuenca:</strong></td>
                                <td class="sin-borde" width="12%">{{ $predio->cuenca }}</td>
                                <td class="sin-borde" width="8%"><strong>Localidad:</strong></td>
                                <td class="sin-borde" width="12%">{{ $predio->localidad }}</td>
                                <td class="sin-borde" width="8%"><strong>Departamento:</strong></td>
                                <td class="sin-borde" width="12%">{{ $municipio->departamento }}</td>
                                <td class="sin-borde" width="8%"><strong>Provincia:</strong></td>
                                <td class="sin-borde" width="12%">{{ $municipio->provincia }}</td>
                                <td class="sin-borde" width="8%"><strong>Distrito:</strong></td>
                                <td class="sin-borde">{{ $municipio->distrito }}</td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
        </div>
    </div>

    <div class="box">
        <div class="box-header">III. PROPIETARIOS DEL PREDIO</div>
        <table>
            <thead>
                <tr>
                    <th>Nombre/Razón Social</th>
                    <th>Documento</th>
                    <th>Dirección</th>
                    <th>Porcentaje</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($predio->propietarios as $propietario)
                    <tr>
                        <td>{{ $propietario->razon_social ?? "" }} {{ $propietario->apellidos ?? "" }} {{ $propietario->nombres ?? "" }}</td>
                        <td>{{ $propietario->tipo_documento }} Nº {{ $propietario->numero_documento ?? "---" }}</td>
                        <td>{{ $propietario->direccion ?? "---" }}</td>
                        <td style="text-align: center;">{{ $propietario->pivot->porcentaje_propiedad ?? 100 }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="box">
        <div class="box-header">IV. DETERMINACIÓN DEL VALOR DEL TERRENO</div>
        <table>
            <thead>
                <tr>
                    @if($predio->tipo_predio == 'urbano')
                        <th>Tipo de predio</th>
                        <th>Tipo de calzada</th>
                        <th>Ancho de vía</th>
                    @else
                        <th>Grupo</th>
                        <th>Distancia</th>
                        <th>Calidad</th>
                    @endif
                    <th>Tiene Agua</th>
                    <th>Tiene Desagüe</th>
                    <th>Tiene Luz</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    @if($predio->tipo_predio == 'urbano')
                        <td>{{ strtoupper($predio->tipo_predio) }}</td>
                        <td>{{ ucfirst($avaluo->tipo_calzada) }}</td>
                        <td>{{ match($avaluo->ancho_via) {
                            'menos_6' => 'Hasta 6 metros',
                            'entre_6_8' => 'Entre 6 y 8 metros',
                            'mas_8' => 'Mas de 8 metros',
                        } }}</td>
                    @else
                        <td>{{ match($avaluo->grupo_tierras) {
                            'A' => '(A) Cultivos en limpio',
                            'C' => '(C) Cultivos permanentes',
                            'P' => '(P) Pastos',
                            'X' => '(X) Eriazas',
                        } }}</td>
                        <td>{{ match($avaluo->distancia) {
                            'hasta_1km' => 'Hasta 1 km',
                            'de_1_2km' => 'Entre 1 y 2 km',
                            'de_2_3km' => 'Entre 2 y 3 km',
                            'mas_3km' => 'Mas de 3 km',
                        } }}</td>
                        <td>{{ ucfirst($avaluo->calidad_agrologica) }}</td>
                    @endif
                    <td width="12%" style="text-align: center;">{{ $avaluo->tiene_agua ? 'Sí' : 'No' }}</td>
                    <td width="12%" style="text-align: center;">{{ $avaluo->tiene_desague ? 'Sí' : 'No' }}</td>
                    <td width="12%" style="text-align: center;">{{ $avaluo->tiene_luz ? 'Sí' : 'No' }}</td>
                </tr>
            </tbody>
        </table>
        <br>
        <table style="margin-top:0; border-top:none;">
            <thead>
                <tr>
                    <th>Área Terreno ({{ $predio->tipo_predio == 'urbano' ? 'm²' : 'ha' }})</th>
                    <th>Arancel (S/.)</th>
                    <th>Valor Terreno (S/.)</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td class="text-center">{{ $predio->tipo_predio == 'urbano' ? $avaluo->area_terreno : $avaluo->area_terreno / 10000 }}</td>
                    <td class="text-center">{{ number_format($avaluo->info_avaluo['terreno']['valor_arancel'] ?? 0, 2, '.', ',') }}</td>
                    <td class="text-right font-bold">
                        {{ number_format($avaluo->info_avaluo['resumen_autoavaluo']['valor_terreno'] ?? 0, 2, '.', ',') }}
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    <div class="box">
        <div class="box-header">V. CARACTERÍSTICAS Y VALOR DE LA CONSTRUCCIÓN</div>
        <table>
            <thead>
                <tr>
                    <th width="5%">Piso</th>
                    <th width="5%">Ant.</th>
                    <th width="10%">Mat.</th>
                    <th width="10%">Est.</th>
                    <th width="5%">Mur</th>
                    <th width="5%">Tec</th>
                    @if($anio < 2020)
                        <th width="5%">Pis</th>
                    @endif
                    <th width="5%">Pue</th>
                    @if($anio < 2020)
                        <th width="5%">Rev</th>
                        <th width="5%">Bañ</th>
                        <th width="5%">Ins</th>
                    @endif

                    <th width="10%">Área Const. (m²)</th>
                    <th width="5%">% Dep</th>
                    <th width="15%">Valor Unit. Dep.</th>
                    <th width="15%">Valor Const.</th>
                </tr>
            </thead>
            <tbody>
                @foreach($predio->construcciones as $piso)
                    @php
                        $infoConstruccion = $avaluo->info_avaluo['construcciones'][$piso->id];
                    @endphp
                    <tr>
                        <td class="text-center">{{ $piso->seccion ?? '' }} {{ $piso->nro_piso }}</td>
                        <td class="text-center">{{ $anio - $piso->anio_construccion }}</td>
                        <td class="text-center">{{ substr($piso->material_estructural, 0, 4) }}.</td>
                        <td class="text-center">{{ substr($piso->estado_conservacion, 0, 3) }}.</td>

                        <td class="text-center">{{ $piso->muros_columnas }}</td>
                        <td class="text-center">{{ $piso->techos }}</td>
                        @if($anio < 2020)
                            <td class="text-center">{{ $piso->pisos }}</td>
                        @endif
                        <td class="text-center">{{ $piso->puertas_ventanas }}</td>
                        @if($anio < 2020)
                            <td class="text-center">{{ $piso->revestimientos }}</td>
                            <td class="text-center">{{ $piso->banos }}</td>
                            <td class="text-center">{{ $piso->inst_electricas_sanitarias }}</td>
                        @endif

                        <td class="text-right">{{ $infoConstruccion['area_total'] }}</td>
                        <td class="text-center">{{ $piso->porcentaje_depreciacion_manual }}%</td>
                        <td class="text-right">
                            S/. {{ number_format($infoConstruccion['valor_unitario_depreciado'], 2, '.', ',') }}
                        </td>
                        <td class="text-right">
                            S/. {{ number_format($infoConstruccion['valor_construccion'], 2, '.', ',') }}
                        </td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td @if ($anio < 2020) colspan="14" @else colspan="10" @endif class="text-right font-bold">Total S/</td>
                    <td class="text-right font-bold" width="15%">{{ number_format($avaluo->info_avaluo['resumen_autoavaluo']['valor_construcciones'] ?? 0, 2, '.', ',') }}</td>
                </tr>
            </tfoot>
        </table>
    </div>

    @if($predio->obrasComplementarias->count() > 0)
        <div class="box">
            <div class="box-header">VI. OBRAS COMPLEMENTARIAS</div>
            <table>
                <thead>
                    <tr>
                        <th width="5%">Ant.</th>
                        <th>Descripción</th>
                        <th width="15%">Cantidad</th>
                        <th width="15%">Valor unitario</th>
                        <th width="15%">Valor total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($predio->obrasComplementarias as $obra)
                        @php
                            $infoObra = $avaluo->info_avaluo['obras_complementarias'][$obra->id];
                        @endphp
                        <tr>
                            <td class="text-center">{{ $anio - $obra->pivot->anio_construccion }}</td>
                            <td>{{ $obra->descripcion }}</td>
                            <td class="text-center">{{ $obra->pivot->cantidad }} {{ $obra->unidad_medida }}</td>
                            <td class="text-center">S/. {{ number_format($infoObra['valor_unitario'] ?? 0, 2, '.', ',') }}</td>
                            <td class="text-center">S/. {{ number_format($infoObra['valor_obra_complementaria'] ?? 0, 2, '.', ',') }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="4" class="text-right font-bold">Total S/</td>
                        <td class="text-right font-bold">{{ number_format($avaluo->info_avaluo['resumen_autoavaluo']['valor_obras_complementarias'] ?? 0, 2, '.', ',') }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    @endif

    <div class="box">
        <div class="box-header">VII. DETERMINACIÓN DEL AUTOAVALÚO</div>
        <table style="width: 50%; float: right;">
            <thead>
                <tr>
                    <th width="67%">Concepto</th>
                    <th width="33%">Valor</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td class="text-right sin-borde"><strong>Valor terreno:</strong></td>
                    <td class="text-right">S/ {{ number_format($avaluo->info_avaluo['resumen_autoavaluo']['valor_terreno'] ?? 0, 2, '.', ',') }}</td>
                </tr>
                <tr>
                    <td class="text-right sin-borde"><strong>Valor construcción:</strong></td>
                    <td class="text-right">S/ {{ number_format($avaluo->info_avaluo['resumen_autoavaluo']['valor_construcciones'] ?? 0, 2, '.', ',') }}</td>
                </tr>
                <tr>
                    <td class="text-right sin-borde"><strong>Valor obras complementarias:</strong></td>
                    <td class="text-right">S/ {{ number_format($avaluo->info_avaluo['resumen_autoavaluo']['valor_obras_complementarias'] ?? 0, 2, '.', ',') }}</td>
                </tr>
                <tr>
                    <td class="text-right sin-borde"><strong>Valor total:</strong></td>
                    <td class="text-right font-bold">S/ {{ number_format($avaluo->info_avaluo['resumen_autoavaluo']['valor_fisico_total'] ?? 0, 2, '.', ',') }}</td>
                </tr>
            </tbody>
        </table>
        <div style="clear: both;"></div>
    </div>

    <div style="margin-top: 30px; font-size: 8px; text-align: center;">
        Este documento es un reporte técnico del estado físico del predio declarado. Emitido el: {{ now()->format('d/m/Y H:i:s') }}
    </div>

</body>

</html>