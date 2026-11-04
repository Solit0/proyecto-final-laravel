# Contrato de datos del caso 4

Estado: diseño acordado para que Miguel escriba las migraciones definitivas. Las fixtures de tests solo reproducen este contrato para comprobar las semillas de Harry.

## Tablas

Todas las tablas incluyen `id` bigint autogenerado y timestamps.

| Tabla | Campo | Tipo / restricción |
|---|---|---|
| laboratorios | nombre | string(100), único, obligatorio |
| laboratorios | ubicacion | string(150), obligatorio |
| laboratorios | capacidad | integer, mayor que cero |
| equipos | laboratorio_id | FK indexada a laboratorios, restrictOnDelete |
| equipos | codigo_inventario | string(8), único, formato LAB-0000 |
| equipos | tipo | computadora, proyector, impresora o switch |
| equipos | marca / modelo / numero_serie | string(100), obligatorios; serie única |
| equipos | fecha_adquisicion | date, no futura |
| equipos | costo | decimal(10,2), no negativo |
| equipos | estado | operativo, en_reparacion o de_baja; inicial operativo |
| equipos | imagen | string nullable; ruta relativa, no archivo binario |
| mantenimientos | equipo_id | FK indexada a equipos, restrictOnDelete |
| mantenimientos | fecha | date, igual o posterior a adquisición |
| mantenimientos | tipo | preventivo o correctivo |
| mantenimientos | descripcion | text, obligatorio |
| mantenimientos | tecnico_responsable | string(150), obligatorio |
| mantenimientos | costo | decimal(10,2), no negativo |

Solo imagen admite null entre los campos del negocio. PostgreSQL requiere CHECK para capacidad positiva y costos no negativos; unsigned no reemplaza esa restricción.

## Relaciones y protección

- Laboratorio `hasMany` equipos; Equipo `belongsTo` laboratorio.
- Equipo `hasMany` mantenimientos; Mantenimiento `belongsTo` equipo.
- No borrar laboratorio utilizado ni equipo con historial. Mostrar un mensaje y permitir marcar el equipo de baja.
- Modelos con fillable y casts; dinero como decimal:2 y fechas como date.

## Validación y estado en Fase 2

- Código exacto LAB-0000 y serie únicos, ignorando el propio equipo al editar.
- No cambiar adquisición a una fecha posterior al mantenimiento más antiguo.
- No registrar mantenimientos para equipos de baja; comprobar estado y adquisición dentro de la transacción bloqueando la fila del equipo.
- Preventivo conserva estado; correctivo cambia a en_reparacion en la misma transacción que la inserción.
- No se define cierre automático de reparación ni prohibición adicional de mantenimientos futuros.

## Semillas

Tres laboratorios y 15 equipos LAB-0001 a LAB-0015, series UNICAES-DEMO-0001 a UNICAES-DEMO-0015. Los tipos y estados se distribuyen entre los valores permitidos. Imagen null; al menos un costo cero. Historial de ejemplo para LAB-0001, LAB-0002 y LAB-0004, respetando sus fechas y excluyendo equipos de baja.
