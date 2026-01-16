<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>LP - Liquidación Predial</title>
    <style>
        @page {
            margin: 1cm;
            font-family: Arial, Helvetica, sans-serif;
        }

        body {
            font-size: 10px;
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
            margin-bottom: 5px;
        }

        td,
        th {
            padding: 4px;
            vertical-align: top;
        }

        /* Bordes específicos */
        .bordered-table td,
        .bordered-table th {
            border: 1px solid #000;
        }

        .no-border {
            border: none !important;
        }

        /* Encabezado LP */
        .box-lp {
            border: 2px solid #000;
            padding: 5px;
            width: 100px;
            float: right;
        }

        .lp-title {
            font-size: 30px;
            font-weight: bold;
            line-height: 1;
        }

        .lp-subtitle {
            font-size: 10px;
            font-weight: bold;
        }

        .lp-muni {
            font-size: 8px;
            font-weight: normal;
            text-transform: uppercase;
        }

        /* Secciones */
        .section-header {
            background-color: #e0e0e0;
            /* Gris claro para separar */
            font-weight: bold;
            border: 1px solid #000;
            padding: 3px;
            margin-top: 10px;
            font-size: 11px;
        }

        .legal-text {
            font-size: 8px;
            text-align: justify;
            margin-top: 10px;
            line-height: 1.2;
        }
    </style>
</head>

<body>

    <table class="w-100 no-border">
        <tr>
            <td width="20%" class="text-center" style="vertical-align: middle;">
                <img src="{{ $municipio_logo }}" alt="{{ $municipio_nombre }}" style="height: 50px;">
                <br>
                <span class="lp-muni">{{ $municipio_nombre }}</span>
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
                <div class="box-lp text-center">
                    <div class="lp-title">LP</div>
                    <div class="lp-subtitle">LIQUIDACIÓN PREDIAL</div>
                </div>
            </td>
        </tr>
    </table>

    <table class="w-100" style="margin-top: 10px; border-bottom: 1px solid #000;">
        <tr>
            <td class="text-left font-bold">
                CÓDIGO DEL CONTRIBUYENTE: {{ $persona->numero_documento }}
            </td>
            <td class="text-right font-bold">
                FECHA DE EMISIÓN: {{ $determinacion->fecha_emision }}
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
        </tbody>
    </table>

    <div class="section-header">SECCIÓN II. DOMICILIO FISCAL</div>
    <table class="bordered-table w-100">
        <tr>
            <td colspan="4">
                <strong>DIRECCIÓN:</strong> {{ $persona->direccion }}
                <strong>ZONA/SECTOR:</strong> {{ $persona->sector ?? '-' }} &nbsp;&nbsp;
                <strong>MANZANA:</strong> {{ $persona->manzana ?? '-' }} &nbsp;&nbsp;
                <strong>LOTE:</strong> {{ $persona->lote ?? '-' }}
            </td>
        </tr>
        <tr>
            <td width="20%">
                <strong>DEPARTAMENTO:</strong><br>{{ $persona->ubicacion_geografica['departamento'] ?? '-' }}
            </td>
            <td width="20%"><strong>PROVINCIA:</strong><br>{{ $persona->ubicacion_geografica['provincia'] ?? '-' }}</td>
            <td width="20%"><strong>DISTRITO:</strong><br>{{ $persona->ubicacion_geografica['distrito'] ?? '-' }}</td>
            <td width="40%">
                <strong>CUENCA/LOCALIDAD:</strong><br>{{ $persona->ubicacion_geografica['cuenca'] ?? '-' }} /
                {{ $persona->ubicacion_geografica['localidad'] ?? '-' }}
            </td>
        </tr>
    </table>

    <div class="section-header">SECCIÓN III. LIQUIDACIÓN DEL IMPUESTO</div>
    <table class="bordered-table w-100">
        <thead>
            <tr style="background-color: #f0f0f0; font-size: 9px;">
                <th width="5%">N° Total Predios</th>
                <th width="5%">N° Predios Afectos</th>
                <th width="12%">Base Imponible Total</th>
                <th width="12%">Base Imponible Afecta (Deducción)</th>
                <th width="15%">Tramo (UIT)</th>
                <th width="12%">Base x Tramo</th>
                <th width="8%">Alícuota</th>
                <th width="10%">Impuesto x Tramo</th>
            </tr>
        </thead>
        <tbody>
            {{--
            LÓGICA VISUAL:
            Usamos rowspan para las columnas generales que no cambian por tramo
            (Total predios, Base Total, Base Afecta).
            --}}

            @php
                // Estos datos deberían venir del controlador/servicio
                // Ejemplo de estructura esperada en $tramos
                $tramos = $determinacion->snapshot_datos['tramos'] ?? [];
                // Si no hay tramos calculados, mostramos filas vacías por diseño
                $rows = max(count($tramos), 1);
            @endphp

            @foreach($tramos as $index => $tramo)
                <tr>
                    @if($index === 0)
                        <td rowspan="{{ count($tramos) }}" class="text-center" style="vertical-align: middle;">
                            {{ $determinacion->cantidad_predios }}
                        </td>
                        <td rowspan="{{ count($tramos) }}" class="text-center" style="vertical-align: middle;">
                            {{-- Ajustar si hay predios inafectos --}}
                            {{ $determinacion->cantidad_predios }}
                        </td>
                        <td rowspan="{{ count($tramos) }}" class="text-right" style="vertical-align: middle;">
                            {{ number_format($determinacion->base_imponible, 2) }}
                        </td>
                        <td rowspan="{{ count($tramos) }}" class="text-right" style="vertical-align: middle;">
                            {{ number_format($determinacion->resumen_economico['base_imponible_afecta'] ?? $determinacion->base_imponible, 2) }}
                            <br>
                            @if(($determinacion->resumen_economico['total_deduccion_base'] ?? 0) > 0)
                                <small>(Ded:
                                    {{ number_format($determinacion->resumen_economico['total_deduccion_base'], 2) }})</small>
                            @endif
                        </td>
                    @endif

                    <td class="text-center">{{ $tramo['etiqueta'] ?? 'Hasta 15 UIT' }}</td>
                    <td class="text-right">{{ number_format($tramo['base_tramo'] ?? 0, 2) }}</td>
                    <td class="text-center">{{ $tramo['alicuota'] ?? '0.2' }}%</td>
                    <td class="text-right">{{ number_format($tramo['impuesto_tramo'] ?? 0, 2) }}</td>
                </tr>
            @endforeach

            @if(empty($tramos))
                <tr>
                    <td colspan="4" class="text-center">-</td>
                    <td>Hasta 15 UIT</td>
                    <td class="text-right">0.00</td>
                    <td class="text-center">0.2%</td>
                    <td class="text-right">0.00</td>
                </tr>
            @endif

            <tr style="background-color: #e0e0e0; font-weight: bold;">
                <td colspan="4" class="no-border text-right">VALOR UIT {{ $anio }}: S/.
                    {{ number_format($determinacion->valor_uit, 2) }}
                </td>
                <td colspan="3" class="text-right">IMPUESTO ANUAL CALCULADO:</td>
                <td class="text-right">S/. {{ number_format($determinacion->impuesto_calculado, 2) }}</td>
            </tr>
            <tr style="font-weight: bold;">
                <td colspan="7" class="text-right no-border">IMPUESTO MINIMO (0.6% UIT):</td>
                <td class="text-right">S/. {{ number_format($determinacion->tasa_minima, 2) }}</td>
            </tr>
        </tbody>
    </table>

    <div class="section-header">SECCIÓN IV. MONTOS A PAGAR A LA FECHA DE EMISIÓN S/.</div>
    <table class="bordered-table w-100">
        <thead>
            <tr style="background-color: #f0f0f0;">
                <th width="10%">CUOTA</th>
                <th width="15%">FECHA VENCIMIENTO</th>
                <th width="15%">MONTO INSOLUTO</th>
                <th width="15%">DERECHO DE EMISIÓN</th>
                <th width="15%">TOTAL A PAGAR</th>
                <th width="20%">CÓDIGO DE PAGO</th>
            </tr>
        </thead>
        <tbody>
            @foreach($cronograma as $cuota)
                <tr>
                    <td class="text-center font-bold">{{ $cuota['numero'] }}</td>
                    <td class="text-center">
                        {{ \Carbon\Carbon::parse($cuota['vence'])->format('d/m/Y') }}
                    </td>
                    <td class="text-right">{{ number_format($cuota['monto'], 2) }}</td>
                    <td class="text-right">
                        {{ number_format($cuota['emision'], 2) }}
                    </td>
                    <td class="text-right font-bold">
                        {{ number_format($cuota['total_cuota'], 2) }}
                    </td>
                    <td class="text-center" style="font-size: 9px;">
                        {{-- Código de barras o referencia de pago --}}
                        {{ $anio }}-{{ $persona->numero_documento }}-C{{ $cuota['numero'] }}
                    </td>
                </tr>
            @endforeach

            <tr style="background-color: #e0e0e0; font-weight: bold;">
                <td colspan="4" class="text-right">TOTAL ANUAL S/</td>
                <td class="text-right">
                    {{ number_format($impuesto_anual + ($cronograma[0]['emision'] ?? 0), 2) }}
                </td>
                <td></td>
            </tr>
        </tbody>
    </table>

    <div class="legal-text">
        <p>
            El impuesto anual no podrá ser inferior a 0.6% de la UIT vigente al 01/01/{{ $anio }} (Art. 13 del TUO de la
            Ley de Tributación Municipal).
            Si paga en cuotas trimestrales, tenga en cuenta que, a partir de la segunda cuota, se aplicará un reajuste
            de acuerdo con la variación acumulada del Índice de Precios al Por Mayor
            (IPM) que publica el Instituto Nacional de Estadística e Informática (INEI), por el período comprendido
            desde el mes de vencimiento de pago de la primera cuota y el mes precedente
            al pago (Art.15 del TUO de la Ley de Tributación Municipal).
        </p>
        <p>
            Para el caso de contribuyentes que se les haya dado el beneficio de pensionista o adulto mayor no
            pensionista, se le deducirá de la base imponible el monto de 50 UIT para obtener
            la base imponible afecta, de acuerdo con el artículo 19 del TUO de la Ley de Tributación Municipal y
            modificatorias, así como a lo dispuesto en el Decreto Supremo N.° 401-2016-EF.
        </p>
        <p>
            La actualización de valores se realiza en aplicación del artículo 14 del TUO de la Ley de Tributación
            Municipal, y se considerará como la declaración jurada del contribuyente para el
            presente año, en tanto no formule objeción alguna hasta el último día hábil del mes de febrero. En caso
            formule objeción, sin presentar la declaración jurada correspondiente, se
            configurará la infracción indicada en el numeral 1) del artículo 176 del TUO del Código Tributario (D.S. N.°
            133-2013-EF y modificatorias), sancionable con multa tributaria.
        </p>
        <p>
            <strong>Importancia del pago puntual:</strong> Por cada cuota vencida se aplicarán intereses moratorios (
            <Ord.TI>), así como gastos y costas procesales de iniciarse el procedimiento de cobranza
                coactiva (TUO del Código Tributario y TUO de la Ley de Procedimiento de Ejecución Coactiva, aprobado
                mediante D.S N.° 018-2008-JUS).
        </p>
    </div>

</body>

</html>