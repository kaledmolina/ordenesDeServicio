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
            <h2>¡Técnico en Camino!</h2>
        </div>
        <div class="content">
            <p>Hola <strong>{{ $orden->nombre_cliente }}</strong>,</p>
            <p>Te informamos que tu Orden de Servicio <strong>#{{ $orden->numero_orden }}</strong> está en proceso.</p>
            
            <p>Nuestro técnico <strong>{{ $orden->technician->name ?? 'asignado' }}</strong> ha iniciado la ruta hacia tu domicilio.</p>

            <p>Por favor, asegúrate de que haya alguien disponible para recibirlo.</p>

            <div style="text-align: center;">
                <a href="{{ route('home') }}" class="button">Seguir en Tiempo Real</a>
            </div>
        </div>
        <div class="footer">
            <p>&copy; {{ date('Y') }} Ordenes de Servicio. Todos los derechos reservados.</p>
        </div>
    </div>
</body>
</html>
