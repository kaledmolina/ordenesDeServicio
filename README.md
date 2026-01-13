<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## About Laravel

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

- [Simple, fast routing engine](https://laravel.com/docs/routing).
- [Powerful dependency injection container](https://laravel.com/docs/container).
- Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
- Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
- Database agnostic [schema migrations](https://laravel.com/docs/migrations).
- [Robust background job processing](https://laravel.com/docs/queues).
- [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

Laravel is accessible, powerful, and provides tools required for large, robust applications.

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework.

You may also try the [Laravel Bootcamp](https://bootcamp.laravel.com), where you will be guided through building a modern Laravel application from scratch.

If you don't feel like reading, [Laracasts](https://laracasts.com) can help. Laracasts contains thousands of video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

## Laravel Sponsors

We would like to extend our thanks to the following sponsors for funding Laravel development. If you are interested in becoming a sponsor, please visit the [Laravel Partners program](https://partners.laravel.com).

### Premium Partners

- **[Vehikl](https://vehikl.com)**
- **[Tighten Co.](https://tighten.co)**
- **[Kirschbaum Development Group](https://kirschbaumdevelopment.com)**
- **[64 Robots](https://64robots.com)**
- **[Curotec](https://www.curotec.com/services/technologies/laravel)**
- **[DevSquad](https://devsquad.com/hire-laravel-developers)**
- **[Redberry](https://redberry.international/laravel-development)**
- **[Active Logic](https://activelogic.com)**

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

---

# Flujo Completo del Sistema de Órdenes de Servicio

Este documento detalla el ciclo de vida completo de una orden de servicio, desde su creación en la oficina hasta la calificación final por parte del cliente.

## 1. Oficina (Panel Administrativo)
**Actor:** Administrador / Operador
**Plataforma:** Panel Web (FilamentPHP)

### A. Creación de la Orden
1.  **Ingreso:** El administrativo ingresa al módulo "Órdenes de Servicio".
2.  **Nueva Orden:** Clic en "Crear Orden".
3.  **Datos del Cliente:**
    *   Selecciona un cliente existente (buscando por nombre o cédula).
    *   El sistema autocompleta dirección, teléfono y precinto.
    *   *Validación:* No permite crear orden si el cliente ya tiene una activa o está en mora (Estado Internet: R).
4.  **Datos del Servicio:**
    *   Selecciona el **Tipo de Orden** (ej. Instalación, Reparación).
    *   Define **Fecha y Clasificación** (Rápida o Cuadrilla).
5.  **Guardado:** La orden se crea con estado **PENDIENTE**.

### B. Asignación
1.  **Tablero de Control:** La orden aparece en la lista como "Pendiente" (gris).
2.  **Asignación:**
    *   El administrativo usa la acción "Asignar Técnico".
    *   Selecciona un técnico disponible del listado.
3.  **Notificación:** El sistema cambia el estado a **ASIGNADA** (amarillo) y registra la fecha de asignación.

---

## 2. Técnico (Aplicación Móvil)
**Actor:** Técnico
**Plataforma:** App Móvil (Flutter)

### A. Recepción y Ruta
1.  **Sincronización:** El técnico ve la nueva orden en la pestaña "Mis Ordenes" o "Pendientes" (si requiere auto-asignarse).
2.  **Detalle:** Revisa los datos: cliente, dirección, **teléfonos** (incluyendo el nuevo campo "Otro Teléfono" y celular facturación), y el problema reportado.
3.  **Inicio de Ruta:**
    *   Clic en **"INICIAR RUTA"**.
    *   El estado cambia a **EN PROCESO** (azul).
    *   *Nota:* Esto indica que el técnico va en camino.

### B. En Sitio
1.  **Llegada:** Al llegar al domicilio, el técnico presiona **"ESTOY EN SITIO"**.
2.  **Confirmación:** El estado cambia a **EN SITIO** (índigo).
3.  **Ejecución:** El técnico realiza el trabajo (reparación, instalación, etc.).

### C. Finalización y Cierre
1.  **Formulario de Cierre:** Clic en **"FINALIZAR ATENCIÓN"**.
2.  **Datos Requeridos:**
    *   **Diagnóstico:** Selecciona la solución aplicada (ej. Cambio de equipo, Reinicio).
    *   **Evidencia:** Toma fotos del trabajo realizado (obligatorio subir al menos una o según política).
    *   **Equipos:** Escanea/Ingresa MACs de equipos instalados o retirados.
    *   **Materiales:** Registra cable, conectores, etc. utilizados.
    *   **Firmas:** Recoge la firma digital del técnico y del suscriptor en la pantalla.
3.  **Envío:** Al guardar, la orden pasa a estado **EJECUTADA** (verde). Desaparece de la lista de pendientes del técnico y queda en el historial.

*Opción Alterna:* Si no se puede completar, puede "Solicitar Cierre" o "Reprogramar", lo cual envía una alerta a la oficina.

---

## 3. Cliente (Seguimiento Web)
**Actor:** Cliente Final
**Plataforma:** Página Web Pública (Landing Page)

### A. Consulta
1.  **Acceso:** El cliente ingresa a la página principal.
2.  **Búsqueda:** Digita su número de cédula o número de orden en el buscador.
3.  **Resultados:** Ve una tarjeta con el estado de su orden en tiempo real.

### B. Línea de Tiempo (Stepper)
*   Visualiza el progreso:
    1.  🕒 Pendiente / Asignada
    2.  🚚 Técnico en Camino (En Proceso)
    3.  🛠️ Técnico en Sitio
    4.  ✅ Finalizada

### C. Calificación (Feedback)
1.  **Habilitación:** Una vez la orden está **EJECUTADA**, aparece un botón o opción para "Calificar Servicio".
2.  **Encuesta:**
    *   Califica con estrellas (1-5).
    *   Deja un comentario opcional sobre la atención.
3.  **Registro:** Esta calificación llega al panel administrativo para control de calidad.

---

## 4. Cierre Administrativo
1.  **Revisión Final:** La oficina filtra las órdenes "Ejecutadas".
2.  **Verificación:** Revisa fotos, firmas y materiales reportados por el técnico.
3.  **Cierre Definitivo:** Cambia el estado a **CERRADA** (rojo/final). Esto concluye el ciclo contable y operativo de la orden.
