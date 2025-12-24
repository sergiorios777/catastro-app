<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Arqueo de Caja</title>
    <style>
        body {
            font-family: sans-serif;
            font-size: 11px;
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
        }

        .titulo {
            font-size: 16px;
            font-weight: bold;
            background: #eee;
            padding: 5px;
            border: 1px solid #000;
        }

        .box-info {
            width: 100%;
            margin-bottom: 20px;
            border: 1px solid #ccc;
            padding: 10px;
        }

        .row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 5px;
        }

        .label {
            font-weight: bold;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        .table th,
        .table td {
            border: 1px solid #999;
            padding: 5px;
            text-align: left;
            font-size: 10px;
        }

        .table th {
            background-color: #f0f0f0;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .totales-box {
            width: 40%;
            margin-left: auto;
            margin-top: 20px;
            border: 2px solid #000;
            padding: 10px;
        }

        .total-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 5px;
            font-size: 12px;
        }

        .gran-total {
            font-weight: bold;
            font-size: 14px;
            border-top: 1px solid #000;
            padding-top: 5px;
        }

        .firmas {
            margin-top: 80px;
            width: 100%;
            text-align: center;
        }

        .firma-line {
            border-top: 1px solid #000;
            width: 200px;
            display: inline-block;
            margin: 0 50px;
        }
    </style>
</head>

<body>

    <div class="header">
        @if($municipio->logo)
            <img src="{{ public_path('storage/' . $municipio->logo) }}" style="width: 60px;">
        @endif
        <div class="bold">{{ strtoupper($municipio->name) ?? 'MUNICIPALIDAD' }}</div>
        <div>RUC: {{ $municipio->ruc ?? '-----------' }}</div>
        @if($municipio->direccion_fiscal)
            <div style="font-size: 9px;">
                {{ $municipio->direccion_fiscal }}
            </div>
        @endif
        <div class="titulo">REPORTE DE CIERRE DE CAJA</div>
        <div>Fecha de Impresión: {{ now()->format('d/m/Y H:i:s') }}</div>
    </div>

    <div class="box-info">
        <table width="100%">
            <tr>
                <td><span class="label">Cajero:</span> {{ $caja->cajero->name }}</td>
                <td><span class="label">Estado:</span> {{ strtoupper($caja->estado) }}</td>
            </tr>
            <tr>
                <td><span class="label">Apertura:</span> {{ $caja->fecha_apertura->format('d/m/Y H:i') }}</td>
                <td><span class="label">Cierre:</span>
                    {{ $caja->fecha_cierre ? $caja->fecha_cierre->format('d/m/Y H:i') : '---' }}</td>
            </tr>
        </table>
    </div>

    <h3>Detalle de Ingresos</h3>
    <table class="table">
        <thead>
            <tr>
                <th>Hora</th>
                <th>Recibo</th>
                <th>Contribuyente</th>
                <th>Medio Pago</th>
                <th class="text-right">Importe</th>
            </tr>
        </thead>
        <tbody>
            @foreach($pagos as $pago)
                <tr>
                    <td>{{ $pago->fecha_pago->format('H:i') }}</td>
                    <td>{{ $pago->serie }}-{{ $pago->numero }}</td>
                    <td>
                        {{ $pago->determinacion->persona->apellidos ?? '' }}
                        {{ $pago->determinacion->persona->nombres ?? '' }}
                    </td>
                    <td class="text-center">{{ strtoupper($pago->metodo_pago) }}</td>
                    <td class="text-right">{{ number_format($pago->monto_total, 2) }}</td>
                </tr>
            @endforeach

            @if($pagos->isEmpty())
                <tr>
                    <td colspan="5" class="text-center">Sin movimientos registrados</td>
                </tr>
            @endif
        </tbody>
    </table>

    <div class="totales-box">
        <div class="total-row">
            <span>(+) Monto Apertura (Sencillo):</span>
            <span>{{ number_format($caja->monto_apertura, 2) }}</span>
        </div>
        <div class="total-row">
            <span>(+) Total Recaudado (Ventas):</span>
            <span>{{ number_format($caja->total_recaudado, 2) }}</span>
        </div>
        <div class="total-row gran-total" style="border-top: 1px dashed #000;">
            <span>(=) TOTAL SISTEMA:</span>
            <span>{{ number_format($resumen['total_sistema'], 2) }}</span>
        </div>

        <br>

        <div class="total-row">
            <span>(-) Dinero en Mano (Declarado):</span>
            <span>{{ number_format($caja->monto_cierre, 2) }}</span>
        </div>

        <div class="total-row gran-total"
            style="color: {{ $resumen['diferencia'] < 0 ? 'red' : ($resumen['diferencia'] > 0 ? 'blue' : 'black') }}">
            <span>DIFERENCIA ({{ $resumen['estado_cuadre'] }}):</span>
            <span>{{ number_format($resumen['diferencia'], 2) }}</span>
        </div>
    </div>

    <div class="firmas">
        <div class="firma-line">
            {{ $caja->cajero->name }}<br>
            CAJERO
        </div>
        <div class="firma-line">
            V° B° TESORERÍA<br>
            SUPERVISOR
        </div>
    </div>

</body>

</html>