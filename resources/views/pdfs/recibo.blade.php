<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Recibo de Ingreso</title>
    <style>
        body {
            font-family: sans-serif;
            font-size: 12px;
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #000;
            padding-bottom: 10px;
        }

        .municipio-nombre {
            font-size: 16px;
            font-weight: bold;
            text-transform: uppercase;
        }

        .titulo-doc {
            font-size: 18px;
            font-weight: bold;
            margin-top: 10px;
            border: 1px solid #000;
            padding: 5px;
            display: inline-block;
        }

        .info-box {
            width: 100%;
            margin-bottom: 15px;
        }

        .info-row {
            margin-bottom: 5px;
        }

        .label {
            font-weight: bold;
            display: inline-block;
            width: 120px;
        }

        .table-detalle {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        .table-detalle th,
        .table-detalle td {
            border: 1px solid #000;
            padding: 8px;
            text-align: left;
        }

        .table-detalle th {
            background-color: #f0f0f0;
        }

        .text-right {
            text-align: right;
        }

        .footer {
            margin-top: 50px;
            text-align: center;
            font-size: 10px;
        }

        .firma-box {
            margin-top: 60px;
            display: flex;
            justify-content: space-between;
        }

        .firma-line {
            border-top: 1px solid #000;
            width: 200px;
            margin: 0 auto;
            padding-top: 5px;
        }

        /* Agrega esto para el logo */
        .logo-img {
            max-width: 80px;
            max-height: 80px;
            position: absolute;
            left: 0;
            top: 0;
        }

        .header-content {
            text-align: center;
        }
    </style>
</head>

<body>

    <div class="header">
        @php
            $logoData = null;
            // 1. Verificamos si hay logo en la base de datos
            if ($municipio->logo) {
                // 2. Obtenemos la ruta FÍSICA real (sin pasar por el link público)
                // Filament guarda en storage/app/public por defecto
                $path = storage_path('app/public/' . $municipio->logo);

                // 3. Verificamos si el archivo existe en el disco
                if (file_exists($path)) {
                    // 4. Leemos el archivo y lo convertimos a Base64
                    $type = pathinfo($path, PATHINFO_EXTENSION);
                    $data = file_get_contents($path);
                    $logoData = 'data:image/' . $type . ';base64,' . base64_encode($data);
                }
            }
        @endphp
        @if($logoData)
            <img src="{{ $logoData }}" class="logo-img" alt="Logo Municipal">
        @else
            <div class="logo-img"
                style="border: 1px dashed #ccc; display: flex; align-items: center; justify-content: center; font-size: 9px;">
                Sin Logo
            </div>
        @endif

        <div class="municipio-nombre">{{ $municipio->name }}</div>
        @if($municipio->slogan)
            <div style="font-style: italic; font-size: 10px; margin-bottom: 5px;">
                "{{ $municipio->slogan }}"
            </div>
        @endif

        <div>RUC: {{ $municipio->ruc ?? '-----------' }}</div>
        @if($municipio->direccion_fiscal)
            <div style="font-size: 9px;">
                {{ $municipio->direccion_fiscal }}
            </div>
        @endif
        <div class="titulo-doc">RECIBO DE INGRESO</div>
        <div>N° {{ $pago->serie }} - {{ $pago->numero }}</div>
    </div>

    <div class="info-box">
        <div class="info-row">
            <span class="label">Fecha:</span> {{ $pago->fecha_pago->format('d/m/Y H:i A') }}
        </div>
        <div class="info-row">
            <span class="label">Contribuyente:</span> {{ $contribuyente->apellidos }} {{ $contribuyente->nombres }}
        </div>
        <div class="info-row">
            <span class="label">Documento:</span> {{ $contribuyente->numero_documento }}
        </div>
        <div class="info-row">
            <span class="label">Dirección:</span> {{ $contribuyente->direccion_fiscal ?? 'No registrada' }}
        </div>
    </div>

    <table class="table-detalle">
        <thead>
            <tr>
                <th>Concepto</th>
                <th>Periodo</th>
                <th class="text-right">Importe</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>IMPUESTO PREDIAL - PAGO TOTAL</td>
                <td>{{ $pago->determinacion->anioFiscal->anio }}</td>
                <td class="text-right">S/. {{ number_format($pago->monto_total, 2) }}</td>
            </tr>
            <tr>
                <td colspan="2" class="text-right"><strong>TOTAL PAGADO</strong></td>
                <td class="text-right"><strong>S/. {{ number_format($pago->monto_total, 2) }}</strong></td>
            </tr>
        </tbody>
    </table>

    <div style="margin-top: 15px;">
        <strong>Son:</strong> {{ $pago->monto_total }} SOLES (Convertir a letras aquí si deseas)
        <br>
        <strong>Medio de Pago:</strong> {{ strtoupper($pago->metodo_pago) }}
        @if($pago->referencia_pago) (Ref: {{ $pago->referencia_pago }}) @endif
    </div>

    <div class="footer">
        <div class="firma-line">
            {{ $pago->procesador->name }}<br>
            CAJERO RESPONSABLE
        </div>
        <br>
        <small>Impreso el {{ now()->format('d/m/Y H:i:s') }}</small>
    </div>

</body>

</html>