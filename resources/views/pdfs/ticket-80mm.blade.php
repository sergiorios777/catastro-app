<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Ticket</title>
    <style>
        @page {
            margin: 5px;
        }

        body {
            font-family: 'Courier New', Courier, monospace;
            /* Fuente tipo Ticket */
            font-size: 10px;
            font-weight: bold;
            margin: 0;
            padding: 0;
            color: #000;
        }

        .center {
            text-align: center;
        }

        .left {
            text-align: left;
        }

        .right {
            text-align: right;
        }

        .bold {
            font-weight: bolder;
        }

        .line {
            border-bottom: 1px dashed #000;
            margin: 5px 0;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 5px;
        }

        .table td {
            vertical-align: top;
        }

        .titulo {
            font-size: 12px;
            font-weight: bold;
        }

        .info {
            margin-bottom: 5px;
        }
    </style>
</head>

<body>
    <div class="center">
        <div class="titulo">{{ strtoupper($municipio->nombre) ?? 'MUNICIPALIDAD' }}</div>
        <div>RUC: {{ $municipio->ruc ?? '-----------' }}</div>
        @if($municipio->direccion_fiscal)
            <div style="font-size: 9px;">
                {{ Str::limit($municipio->direccion_fiscal, 40) }}
            </div>
        @endif
        <div class="line"></div>
        <div class="bold">RECIBO DE INGRESO</div>
        <div>{{ $pago->serie }} - {{ $pago->numero }}</div>
        <div>{{ $pago->fecha_pago->format('d/m/Y H:i:s') }}</div>
    </div>

    <div class="line"></div>

    <div class="info">
        <span class="bold">CONTRIBUYENTE:</span><br>
        {{ $contribuyente->apellidos }} {{ $contribuyente->nombres }}<br>
        {{ $contribuyente->numero_documento }}
    </div>

    <div class="line"></div>

    <table class="table">
        <tr>
            <td class="left bold">DESC</td>
            <td class="right bold">IMP</td>
        </tr>
        <tr>
            <td class="left">Impuesto Predial {{ $pago->determinacion->anioFiscal->anio }}</td>
            <td class="right">{{ number_format($pago->monto_total, 2) }}</td>
        </tr>
    </table>

    <div class="line"></div>

    <table class="table">
        <tr>
            <td class="right bold">TOTAL: S/. {{ number_format($pago->monto_total, 2) }}</td>
        </tr>
    </table>

    <br>
    <div class="info">
        Son: {{ $pago->monto_total }} Soles<br>
        Pago: {{ strtoupper($pago->metodo_pago) }}
    </div>

    <div class="center" style="margin-top: 15px;">
        -----------------------<br>
        {{ $pago->procesador->name }}<br>
        Cajero
    </div>

    <div class="center" style="margin-top: 10px;">
        ¡Gracias por su contribución!
    </div>
</body>

</html>