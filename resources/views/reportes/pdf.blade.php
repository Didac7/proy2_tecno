<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Reporte Trans Velasco</title>
    <style>
        body {
            font-family: sans-serif;
            color: #333;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #6c63ff;
            padding-bottom: 10px;
        }
        .header h1 {
            color: #6c63ff;
            margin: 0;
        }
        .header p {
            margin: 5px 0;
            color: #666;
        }
        .stats-container {
            display: table;
            width: 100%;
            margin-bottom: 30px;
        }
        .stat-box {
            display: table-cell;
            width: 33%;
            text-align: center;
            padding: 10px;
            background-color: #f8f9fa;
            border: 1px solid #ddd;
        }
        .stat-value {
            font-size: 24px;
            font-weight: bold;
            color: #6c63ff;
        }
        .stat-label {
            font-size: 12px;
            text-transform: uppercase;
            color: #666;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            padding: 10px;
            border-bottom: 1px solid #ddd;
            text-align: left;
            font-size: 12px;
        }
        th {
            background-color: #6c63ff;
            color: white;
        }
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .footer {
            position: fixed;
            bottom: 0;
            width: 100%;
            text-align: center;
            font-size: 10px;
            color: #999;
            border-top: 1px solid #ddd;
            padding-top: 10px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>TRANS VELASCO</h1>
        <p>Reporte de Gestión - {{ now()->format('d/m/Y H:i') }}</p>
    </div>

    <div class="stats-container">
        <div class="stat-box">
            <div class="stat-value">Bs. {{ number_format($stats['ingresos_hoy'], 2) }}</div>
            <div class="stat-label">Ingresos Hoy</div>
        </div>
        <div class="stat-box">
            <div class="stat-value">{{ $stats['paquetes_hoy'] }}</div>
            <div class="stat-label">Paquetes Hoy</div>
        </div>
        <div class="stat-box">
            <div class="stat-value">{{ $stats['clientes_nuevos_hoy'] }}</div>
            <div class="stat-label">Nuevos Clientes</div>
        </div>
    </div>

    <h3>Resumen de Paquetes por Estado</h3>
    <table>
        <thead>
            <tr>
                <th>Estado</th>
                <th>Cantidad</th>
            </tr>
        </thead>
        <tbody>
            @foreach($paquetesPorEstado as $estado)
            <tr>
                <td>{{ $estado->estado_paquete }}</td>
                <td>{{ $estado->total }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <h3>Top 5 Rutas Más Usadas</h3>
    <table>
        <thead>
            <tr>
                <th>Origen</th>
                <th>Destino</th>
                <th>Envíos Totales</th>
            </tr>
        </thead>
        <tbody>
            @foreach($topRutas as $ruta)
            <tr>
                <td>{{ $ruta->origen->ciudad }}</td>
                <td>{{ $ruta->destino->ciudad }}</td>
                <td>{{ $ruta->paquetes_count }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        <p>Generado por Sistema Trans Velasco | Usuario: {{ auth()->user()->nombre }} {{ auth()->user()->apellido }}</p>
    </div>
</body>
</html>
