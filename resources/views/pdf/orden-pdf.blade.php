{{--
  Ruta del archivo: resources/views/pdf/orden-pdf.blade.php
--}}
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Orden de Servicio #{{ $orden->numero_orden }}</title>
    <style>
        body { font-family: 'Helvetica', sans-serif; font-size: 11px; color: #333; }
        .container { width: 100%; margin: 0 auto; padding: 0; }
        .header { text-align: center; margin-bottom: 25px; }
        .header h1 { margin: 0; font-size: 22px; color: #000; }
        .header p { margin: 5px 0 0; font-size: 14px; }
        .section { margin-bottom: 15px; border: 1px solid #ddd; padding: 10px; border-radius: 8px; page-break-inside: avoid; }
        .section-title { font-size: 14px; font-weight: bold; margin-bottom: 10px; background-color: #eee; padding: 8px; border-radius: 5px; color: #000; }
        table { width: 100%; border-collapse: collapse; }
        td { padding: 6px; vertical-align: top; border-bottom: 1px solid #f2f2f2; }
        .label { font-weight: bold; width: 35%; }
        .value { width: 65%; }
        .main-table { width: 100%; border-spacing: 10px 0; border-collapse: separate; }
        .main-table > tbody > tr > td { padding: 0; border: none; vertical-align: top; }
        .no-border td { border-bottom: none; }
        .obs { padding: 10px; min-height: 40px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Orden de Servicio</h1>
            <p>Número de Orden: <strong>{{ $orden->numero_orden }}</strong></p>
        </div>

        <table class="main-table">
            <tr>
                <td style="width: 48%;">
                    <div class="section">
                        <div class="section-title">Información Principal</div>
                        <table>
                            <tr><td class="label">Número de Expediente:</td><td class="value">{{ $orden->numero_expediente ?? 'N/A' }}</td></tr>
                            <tr><td class="label">Nombre del Cliente:</td><td class="value">{{ $orden->nombre_cliente }}</td></tr>
                            <tr class="no-border"><td class="label">Fecha y Hora:</td><td class="value">{{ $orden->fecha_hora->format('d/m/Y h:i A') }}</td></tr>
                        </table>
                    </div>
                </td>
                <td style="width: 4%;"></td>
                <td style="width: 48%;">
                    <div class="section">
                        <div class="section-title">Detalles del Servicio</div>
                        <table>
                            <tr><td class="label">Movimiento:</td><td class="value">{{ $orden->movimiento ?? 'N/A' }}</td></tr>
                            <tr><td class="label">Servicio:</td><td class="value">{{ $orden->servicio ?? 'N/A' }}</td></tr>
                            <tr><td class="label">Marca:</td><td class="value">{{ $orden->marca ?? 'N/A' }}</td></tr>
                            <tr class="no-border"><td class="label">Técnico Asignado:</td><td class="value">{{ $orden->technician->name ?? 'Sin asignar' }}</td></tr>
                        </table>
                    </div>
                </td>
            </tr>
        </table>

        <div class="section">
            <div class="section-title">Referencia del Servicio</div>
            <table>
                <tr>
                    <td class="label">Valor del Servicio:</td><td class="value">${{ number_format($orden->valor_servicio, 0, ',', '.') }}</td>
                    <td class="label">Placa:</td><td class="value">{{ $orden->placa ?? 'N/A' }}</td>
                    <td class="label">Referencia:</td><td class="value">{{ $orden->referencia ?? 'N/A' }}</td>
                </tr>
            </table>
        </div>

        <div class="section">
            <div class="section-title">Información de Contacto en Origen</div>
            <table>
                <tr><td class="label">Nombre del Asegurado:</td><td class="value">{{ $orden->nombre_asignado ?? 'N/A' }}</td></tr>
                <tr class="no-border"><td class="label">Celular:</td><td class="value">{{ $orden->celular ?? 'N/A' }}</td></tr>
            </table>
        </div>

        <table class="main-table">
            <tr>
                <td style="width: 48%;">
                    <div class="section">
                        <div class="section-title">Origen</div>
                        <table>
                            <tr><td class="label">Ciudad:</td><td class="value">{{ $orden->ciudad_origen }}</td></tr>
                            <tr><td class="label">Dirección:</td><td class="value">{{ $orden->direccion_origen }}</td></tr>
                            <tr class="no-border"><td class="label">Observaciones:</td><td class="value">{{ $orden->observaciones_origen ?? 'N/A' }}</td></tr>
                        </table>
                    </div>
                </td>
                <td style="width: 4%;"></td>
                <td style="width: 48%;">
                    <div class="section">
                        <div class="section-title">Destino</div>
                        <table>
                            <tr><td class="label">Ciudad:</td><td class="value">{{ $orden->ciudad_destino }}</td></tr>
                            <tr><td class="label">Dirección:</td><td class="value">{{ $orden->direccion_destino }}</td></tr>
                            <tr class="no-border"><td class="label">Observaciones:</td><td class="value">{{ $orden->observaciones_destino ?? 'N/A' }}</td></tr>
                        </table>
                    </div>
                </td>
            </tr>
        </table>

        <div class="section">
            <div class="section-title">Información Adicional</div>
            <div class="obs">
                <p><strong>Observaciones Generales:</strong> {{ $orden->observaciones_generales ?? 'N/A' }}</p>
                <p><strong>Estado:</strong> {{ ucfirst($orden->status) }}</p>
                @if($orden->status === 'programada' && $orden->fecha_programada)
                    <p><strong>Fecha Programada:</strong> {{ $orden->fecha_programada->format('d/m/Y h:i A') }}</p>
                @endif
            </div>
        </div>

        {{-- 👇 SECCIÓN AÑADIDA PARA LAS FOTOS --}}
        <div class="section">
            <div class="section-title">Fotos de la Orden</div>
            <div class="obs">
                @forelse ($orden->fotos as $foto)
                    <p>{{ $foto->path }}</p>
                @empty
                    <p>Sin fotos asignadas.</p>
                @endforelse
            </div>
        </div>
    </div>
</body>
</html>
