# Contratos entre controladores y vistas

Estas son interfaces de integración acordadas; las rutas del negocio todavía no están implementadas. David administra routes/web.php.

| Método / URL | Nombre | Datos de vista / acción | Responsable |
|---|---|---|---|
| GET / | panel.index | Inicio básico F1; indicadores F2 | Alfredo vista, Harry controlador |
| GET /equipos | equipos.index | $equipos paginados de 10; $laboratorios para filtros F2 | David / Alfredo |
| GET /equipos/{equipo} | equipos.show | $equipo con laboratorio y mantenimientos | David / Alfredo |
| GET /equipos/create | equipos.create | $laboratorios y Equipo nuevo | David / Alfredo, F2 |
| POST /equipos | equipos.store | Guardar datos validados y fotografía opcional | David / Harry, F2 |
| GET /equipos/{equipo}/edit | equipos.edit | $equipo y $laboratorios | David / Alfredo, F2 |
| PUT /equipos/{equipo} | equipos.update | Actualizar sin perder imagen actual si no llega otra | David / Harry, F2 |
| DELETE /equipos/{equipo} | equipos.destroy | Restringir historial, eliminar archivo tras éxito | David / Harry, F2 |
| GET /mantenimientos | mantenimientos.index | $mantenimientos paginados con equipo.laboratorio | Miguel / Alfredo, F2 |
| GET /mantenimientos/create | mantenimientos.create | $equipos habilitados, ordenados por código | Miguel / Alfredo, F2 |
| POST /mantenimientos | mantenimientos.store | Registrar y aplicar reglas del estado | Miguel, F2 |
| GET /reportes | reportes.index | Distribución, costos y ausencia de mantenimiento | Harry, F2 |

Equipos usa Route::resource; solo index/show operativos en F1. Parámetros GET de búsqueda F2: q, laboratorio_id y estado. Conservarlos al paginar y agrupar los OR de búsqueda para no anular filtros.

Campos HTML coinciden con [data-model.md](../data-model.md). Mantenimientos usa equipo_id, fecha, tipo, descripcion, tecnico_responsable y costo. Formularios con CSRF; edición/borrado con método correspondiente, archivos con multipart/form-data y límites de imagen acordados.

Mensajes flash: exito y error. Errores de validación en español por campo; conservar old(). Equipo inexistente devuelve 404. Inicio básico no ofrece accesos a módulos pendientes.
