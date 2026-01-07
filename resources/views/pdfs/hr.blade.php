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
        </table>
    </div>

    <div class="box-title">II. RELACIÓN DE PREDIOS</div>
    <table>
        <thead>
            <tr>
                <th width="5%">#</th>
                <th width="10%">Cód. Predio</th>
                <th width="50%">Ubicación del Predio</th>
                <th width="10%">% Prop.</th>
                <th width="15%">Autoavalúo (S/.)</th>
            </tr>
        </thead>
        <tbody>
            @php $i = 1; @endphp
            @foreach($predios as $relacion)
                <tr>
                    <td class="text-center">{{ $i++ }}</td>
                    <td>{{ $relacion->predioFisico->codigo_referencia ?? 'S/N' }}</td>
                    <td>{{ $relacion->predioFisico->direccion }} - {{ $relacion->predioFisico->sector }}</td>
                    <td class="text-center">{{ $relacion->porcentaje_propiedad }}%</td>
                    <td class="text-right">S/. 0.00 (Ref)</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="box">
        <div class="box-title">III. DETERMINACIÓN DEL IMPUESTO</div>
        <table style="width: 50%; float: right; margin-top: 10px;">
            <tr>
                <td><strong>Total Predios Declarados:</strong></td>
                <td class="text-center">{{ $determinacion->cantidad_predios }}</td>
            </tr>
            <tr>
                <td><strong>Base Imponible Total (S/.):</strong></td>
                <td class="text-right">{{ number_format($determinacion->base_imponible, 2) }}</td>
            </tr>
            <tr>
                <td><strong>Impuesto Calculado (Anual):</strong></td>
                <td class="text-right bold" style="font-size: 13px;">S/.
                    {{ number_format($determinacion->impuesto_calculado, 2) }}
                </td>
            </tr>
            <tr>
                <td><strong>Monto Mínimo a Pagar:</strong></td>
                <td class="text-right">{{ number_format($determinacion->tasa_minima, 2) }}</td>
            </tr>
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