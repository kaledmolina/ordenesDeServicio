<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f4f4f4; padding: 20px; }
        .container { background-color: #ffffff; padding: 20px; border-radius: 8px; box-shadow: 0 0 10px rgba(0,0,0,0.1); max-width: 600px; margin: auto; }
        .header { background-color: #10447E; color: white; padding: 15px; text-align: center; border-radius: 8px 8px 0 0; }
        .content { padding: 20px; }
        .footer { text-align: center; font-size: 12px; color: #888; margin-top: 20px; }
        .button { display: inline-block; padding: 10px 20px; background-color: #10447E; color: white; text-decoration: none; border-radius: 5px; margin-top: 15px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>¡Hola, {{ $orden->nombre_cliente }}!</h2>
        </div>
        <div class="content">
            <p>Hemos recibido tu solicitud y se ha creado una nueva Orden de Servicio.</p>
            
            <h3>Detalles de la Orden:</h3>
            <ul>
                <li><strong>Número de Orden:</strong> {{ $orden->numero_orden }}</li>
                <li><strong>Tipo de Servicio:</strong> {{ $orden->tipo_orden }}</li>
                <li><strong>Fecha Programada:</strong> {{ $orden->fecha_programada ? $orden->fecha_programada->format('d/m/Y') : 'Por definir' }}</li>
                <li><strong>Dirección:</strong> {{ $orden->direccion }}</li>
            </ul>

            <p>Nuestro equipo asignará un técnico lo antes posible. Te notificaremos cuando el técnico vaya en camino.</p>

            <div style="text-align: center;">
                <a href="{{ route('home') }}" class="button">Ver Estado de mi Orden</a>
            </div>
        </div>
        <div class="footer">
            <p>&copy; {{ date('Y') }} Ordenes de Servicio. Todos los derechos reservados.</p>
        </div>
    </div>
</body>
</html>
