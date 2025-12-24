<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Cierre de Caja</title>
    <style>
        @page {
            margin: 5px;
        }

        body {
            font-family: 'Courier New', Courier, monospace;
            font-size: 10px;
            font-weight: bold;
            margin: 0;
            padding: 5px;
            color: #000;
        }

        .center {
            text-align: center;
        }

        .right {
            text-align: right;
        }

        .bold {
            font-weight: bold;
        }

        .line {
            border-bottom: 1px dashed #000;
            margin: 5px 0;
        }

        .table {
            width: 100%;
            margin-top: 5px;
        }

        .table td {
            vertical-align: top;
        }

        .big-total {
            font-size: 12px;
            font-weight: bold;
        }
    </style>
</head>

<body>
    <div class="center">
        @if($municipio->logo)
            <img src="{{ public_path('storage/' . $municipio->logo) }}" style="width: 60px;">
        @endif
        <div class="bold">{{ strtoupper($municipio->name) ?? 'MUNICIPALIDAD' }}</div>
        <div>RUC: {{ $municipio->ruc ?? '-----------' }}</div>
        @if($municipio->direccion_fiscal)
            <div style="font-size: 9px;">
                {{ Str::limit($municipio->direccion_fiscal, 40) }}
            </div>
        @endif
        <div></div>CIERRE DE CAJA
    </div>
    <div>{{ now()->format('d/m/Y H:i') }}</div>
    </div>

    <div class="line"></div>

    <div>
        <span class="bold">CAJERO:</span> {{ $caja->cajero->name }}<br>
        <span class="bold">APERTURA:</span> {{ $caja->fecha_apertura->format('d/m H:i') }}<br>
        <span class="bold">CIERRE:</span> {{ $caja->fecha_cierre ? $caja->fecha_cierre->format('d/m H:i') : '---' }}
    </div>

    <div class="line"></div>

    <div class="bold center">RESUMEN DE OPERACION</div>

    <table class="table">
        <tr>
            <td>(+) Fondo Inicial:</td>
            <td class="right">{{ number_format($caja->monto_apertura, 2) }}</td>
        </tr>
        <tr>
            <td>(+) Ventas/Cobros:</td>
            <td class="right">{{ number_format($caja->total_recaudado, 2) }}</td>
        </tr>
        <tr>
            <td class="line" colspan="2"></td>
        </tr>
        <tr>
            <td class="bold">(=) TOTAL SISTEMA:</td>
            <td class="right bold">{{ number_format($resumen['total_sistema'], 2) }}</td>
        </tr>
    </table>

    <br>

    <div class="bold center">ARQUEO DE DINERO</div>
    <table class="table">
        <tr>
            <td>Dinero Declarado:</td>
            <td class="right">{{ number_format($caja->monto_cierre, 2) }}</td>
        </tr>
        <tr>
            <td>Diferencia:</td>
            <td class="right">{{ number_format($resumen['diferencia'], 2) }}</td>
        </tr>
    </table>

    <div class="center" style="margin-top: 10px; border: 1px solid #000; padding: 5px;">
        ESTADO: <span class="bold">{{ $resumen['estado_cuadre'] }}</span>
    </div>

    <div class="line"></div>

    <div class="center" style="margin-top: 25px;">
        -----------------------<br>
        Firma Cajero
    </div>

</body>

</html>