<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inspección Preoperacional #{{ $inspection->id }}</title>
    <style>
        body { font-family: 'Helvetica', sans-serif; font-size: 12px; }
        .container { width: 100%; margin: 0 auto; }
        .header { text-align: center; margin-bottom: 20px; }
        .header h1 { margin: 0; }
        .header p { margin: 5px 0; }
        .section { margin-bottom: 20px; }
        .section-title { background-color: #f2f2f2; padding: 8px; font-weight: bold; border: 1px solid #ddd; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        .info-table td:first-child { font-weight: bold; width: 30%; }
        .checklist-table td:first-child { width: 70%; }
        .checklist-table td:last-child { text-align: center; font-weight: bold; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Reporte de Inspección Preoperacional</h1>
            <p>ID de Inspección: {{ $inspection->id }}</p>
            <p>Fecha de Inspección: {{ $inspection->fecha_inspeccion->format('d/m/Y') }}</p>
        </div>

        <div class="section">
            <div class="section-title">Información General</div>
            <table class="info-table">
                <tr>
                    <td>Nombre del Conductor:</td>
                    <td>{{ $inspection->nombre_conductor }}</td>
                </tr>
                <tr>
                    <td>Número de Licencia:</td>
                    <td>{{ $inspection->licencia_conductor }}</td>
                </tr>
                <tr>
                    <td>Placa del Vehículo:</td>
                    <td>{{ $inspection->placa_vehiculo }}</td>
                </tr>
                 <tr>
                    <td>Marca/Tipo de Vehículo:</td>
                    <td>{{ $inspection->marca_vehiculo }}</td>
                </tr>
                <tr>
                    <td>Modelo del Vehículo:</td>
                    <td>{{ $inspection->modelo_vehiculo }}</td>
                </tr>
                 <tr>
                    <td>Kilometraje Actual:</td>
                    <td>{{ number_format($inspection->kilometraje_actual, 0, ',', '.') }} km</td>
                </tr>
            </table>
        </div>

        <div class="section">
            <div class="section-title">Documentación y Mantenimiento del Vehículo</div>
            <table class="info-table">
                <tr>
                    <td>N° Tarjeta de Propiedad:</td>
                    <td>{{ $inspection->tarjeta_propiedad }}</td>
                </tr>
                <tr>
                    <td>Vencimiento Tecnomecánica:</td>
                    <td>{{ $inspection->fecha_tecnomecanica ? \Carbon\Carbon::parse($inspection->fecha_tecnomecanica)->format('d/m/Y') : 'N/A' }}</td>
                </tr>
                <tr>
                    <td>Vencimiento SOAT:</td>
                    <td>{{ $inspection->fecha_soat ? \Carbon\Carbon::parse($inspection->fecha_soat)->format('d/m/Y') : 'N/A' }}</td>
                </tr>
                <tr>
                    <td>Taller Mantenimiento:</td>
                    <td>{{ $inspection->mantenimiento_preventivo_taller }}</td>
                </tr>
                <tr>
                    <td>Próximo Mantenimiento:</td>
                    <td>{{ $inspection->fecha_mantenimiento ? \Carbon\Carbon::parse($inspection->fecha_mantenimiento)->format('d/m/Y') : 'N/A' }}</td>
                </tr>
                <tr>
                    <td>Último Cambio de Aceite:</td>
                    <td>{{ $inspection->fecha_ultimo_aceite ? \Carbon\Carbon::parse($inspection->fecha_ultimo_aceite)->format('d/m/Y') : 'N/A' }}</td>
                </tr>
            </table>
        </div>

        @php
            $items = [
                'NIVELES' => ['nivel_refrigerante' => 'Líquido Refrigerante', 'nivel_frenos' => 'Líquido de Frenos', 'nivel_aceite_motor' => 'Aceite Motor', 'nivel_hidraulico' => 'Nivel Liq. Hidráulico', 'nivel_limpiavidrios' => 'Agua de Limpiavidrios'],
                'LUCES' => ['luces_altas' => 'Luces Altas', 'luces_bajas' => 'Luces Bajas', 'luces_direccionales' => 'Luces Direccionales', 'luces_freno' => 'Luces de Freno', 'luces_reversa' => 'Luces de Reversa', 'luces_parqueo' => 'Luces de Parqueo'],
                'EQUIPO DE CARRETERA' => ['equipo_extintor' => 'Extintor', 'equipo_tacos' => 'Tacos', 'equipo_herramienta' => 'Caja de Herramienta', 'equipo_linterna' => 'Linterna', 'equipo_gato' => 'Gato', 'equipo_botiquin' => 'Botiquín'],
                'VARIOS' => ['varios_llantas' => 'Llantas', 'varios_bateria' => 'Batería', 'varios_rines' => 'Rines', 'varios_cinturon' => 'Cinturón de Seguridad', 'varios_pito' => 'Pito', 'varios_freno_emergencia' => 'Freno de Emergencia', 'varios_espejos' => 'Espejos', 'varios_plumillas' => 'Plumillas', 'varios_panoramico' => 'Panorámico'],
            ];
        @endphp

        @foreach($items as $sectionTitle => $sectionItems)
        <div class="section">
            <div class="section-title">{{ $sectionTitle }}</div>
            <table class="checklist-table">
                @foreach($sectionItems as $key => $label)
                <tr>
                    <td>{{ $label }}</td>
                    <td>{{ $inspection->$key }}</td>
                </tr>
                @endforeach
            </table>
        </div>
        @endforeach

        <div class="section">
            <div class="section-title">Observaciones</div>
            <p>{{ $inspection->observaciones ?? 'Sin observaciones.' }}</p>
        </div>

    </div>
</body>
</html>