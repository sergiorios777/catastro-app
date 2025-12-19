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
        <h1>FORMATO PU (PREDIO URBANO)</h1>
        <span>AÑO FISCAL {{ $anio }}</span><br>
        <span>MUNICIPALIDAD DE {{ strtoupper($municipio->distrito) }}</span>
    </div>

    <div class="box">
        <div class="box-header">I. DATOS DEL CONTRIBUYENTE</div>
        <div class="box-content">
            @if($persona)
                <strong>Nombre/Razón Social:</strong> {{ $persona->apellidos }} {{ $persona->nombres }}
                {{ $persona->razon_social }} <br>
                <strong>Documento:</strong> {{ $persona->numero_documento }}
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
                    <td class="sin-borde" width="15%"><strong>Cód. Catastral:</strong></td>
                    <td class="sin-borde" width="35%">{{ $predio->cuc ?? '---' }}</td>
                    <td class="sin-borde" width="15%"><strong>Cód. Ref:</strong></td>
                    <td class="sin-borde">{{ $predio->codigo_referencia }}</td>
                </tr>
                <tr class="sin-borde">
                    <td class="sin-borde"><strong>Dirección:</strong></td>
                    <td class="sin-borde" colspan="3">{{ $predio->direccion }} - {{ $predio->sector }}</td>
                </tr>
            </table>
        </div>
    </div>

    <div class="box">
        <div class="box-header">III. DETERMINACIÓN DEL VALOR DEL TERRENO</div>
        <table style="margin-top:0; border-top:none;">
            <thead>
                <tr>
                    <th>Área Terreno (m²)</th>
                    <th>Arancel (S/.)</th>
                    <th>Valor Terreno (S/.)</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td class="text-center">{{ $predio->area_terreno }}</td>
                    <td class="text-center">---</td>
                    <td class="text-right font-bold">
                        S/. (Ver HR)
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    <div class="box">
        <div class="box-header">IV. DESCRIPCIÓN DE LA CONSTRUCCIÓN</div>
        <table>
            <thead>
                <tr>
                    <th width="5%">Piso</th>
                    <th width="5%">Ant.</th>
                    <th width="10%">Mat.</th>
                    <th width="10%">Est.</th>
                    <th width="5%">Mur</th>
                    <th width="5%">Tec</th>
                    <th width="5%">Pis</th>
                    <th width="5%">Pue</th>
                    <th width="5%">Rev</th>
                    <th width="5%">Bañ</th>
                    <th width="5%">Ins</th>

                    <th width="10%">Área Const.</th>
                    <th width="5%">% Dep</th>
                    <th width="15%">Valor Estimado</th>
                </tr>
            </thead>
            <tbody>
                @foreach($predio->construcciones as $piso)
                    <tr>
                        <td class="text-center">{{ $piso->nro_piso }}</td>
                        <td class="text-center">{{ $anio - $piso->anio_construccion }}</td>
                        <td class="text-center">{{ substr($piso->material_estructural, 0, 4) }}.</td>
                        <td class="text-center">{{ substr($piso->estado_conservacion, 0, 3) }}.</td>

                        <td class="text-center">{{ $piso->muros_columnas }}</td>
                        <td class="text-center">{{ $piso->techos }}</td>
                        <td class="text-center">{{ $piso->pisos }}</td>
                        <td class="text-center">{{ $piso->puertas_ventanas }}</td>
                        <td class="text-center">{{ $piso->revestimientos }}</td>
                        <td class="text-center">{{ $piso->banos }}</td>
                        <td class="text-center">{{ $piso->inst_electricas_sanitarias }}</td>

                        <td class="text-right">{{ $piso->area_construida }}</td>
                        <td class="text-center">{{ $piso->porcentaje_depreciacion_manual }}%</td>
                        <td class="text-right">
                            S/. ---
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    @if($predio->obrasComplementarias->count() > 0)
        <div class="box">
            <div class="box-header">V. OBRAS COMPLEMENTARIAS / INSTALACIONES FIJAS</div>
            <table>
                <thead>
                    <tr>
                        <th>Descripción</th>
                        <th width="15%">Cantidad</th>
                        <th width="15%">Material</th>
                        <th width="15%">Estado</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($predio->obrasComplementarias as $obra)
                        <tr>
                            <td>{{ $obra->descripcion }}</td>
                            <td class="text-center">{{ $obra->pivot->cantidad }} {{ $obra->unidad_medida }}</td>
                            <td class="text-center">---</td>
                            <td class="text-center">{{ ucfirst($obra->pivot->estado_conservacion) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

    <div style="margin-top: 30px; font-size: 8px; text-align: center;">
        Este documento es un reporte técnico del estado físico del predio declarado.
    </div>

</body>

</html>