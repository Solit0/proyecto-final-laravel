# Feature Specification: Inventario de laboratorios

**Feature Branch**: `chore/harry-fase-1`
**Created**: 2026-11-03
**Status**: Especificado; implementación actual limitada a la parte de Harry de Fase 1.
**Input**: Caso 4 del PDF y reparto acordado: Miguel datos/mantenimientos, David equipos, Alfredo interfaz y Harry entorno/semillas/imágenes/reportes.

## User Scenarios & Testing

### User Story 1 - Consultar inventario (Priority: P1)

El personal consulta equipos y abre una ficha con laboratorio, fotografía e historial.

**Why this priority**: Resultado visible exigido en Fase 1.
**Independent Test**: Preparar datos de prueba y navegar al menos 15 equipos, dos páginas y una ficha.
**Acceptance Scenarios**:

1. Dado un inventario con 15 equipos y 3 laboratorios, al abrir el listado aparecen datos, miniaturas y paginación.
2. Al abrir una ficha aparecen todos los campos del equipo, su laboratorio y el historial o un mensaje de historial vacío.
3. Sin fotografía disponible, se muestra una imagen predeterminada.

### User Story 2 - Administrar equipos (Priority: P2)

El personal registra, modifica, busca y elimina equipos conservando la integridad del historial.

**Why this priority**: Completa la administración en Fase 2.
**Independent Test**: Crear y editar un equipo válido, buscarlo y eliminarlo si carece de historial.
**Acceptance Scenarios**:

1. Datos inválidos e identificadores repetidos se rechazan con mensajes por campo y valores conservados.
2. Una fotografía válida puede cargarse y reemplazarse; los archivos rechazados no se guardan.
3. Eliminar un equipo con historial se rechaza; su información y fotografía permanecen disponibles.

### User Story 3 - Registrar mantenimientos (Priority: P3)

El personal registra y consulta mantenimientos de cada equipo.

**Why this priority**: Controla historial y estado en Fase 2.
**Independent Test**: Registrar un preventivo y un correctivo y consultar ambos en el historial.
**Acceptance Scenarios**:

1. Un mantenimiento en la adquisición se acepta; uno anterior se rechaza.
2. Un equipo de baja no admite nuevos mantenimientos.
3. Un correctivo deja el equipo en reparación sin escrituras parciales.

### User Story 4 - Consultar reportes (Priority: P4)

El personal revisa distribución, costos y ausencia de mantenimiento reciente.

**Why this priority**: Entrega los tres reportes obligatorios de Fase 2.
**Independent Test**: Comparar resultados con datos de totales y fechas conocidos.
**Acceptance Scenarios**:

1. Se muestran equipos por laboratorio y estado y costo total de mantenimiento por equipo.
2. La lista sin mantenimiento del último año incluye equipos sin historial; un mantenimiento exactamente en la fecha límite sí cuenta dentro del período.

### Edge Cases

- Inventario vacío, búsqueda sin resultados y equipo inexistente.
- Identificadores duplicados, adquisición futura, costo negativo y laboratorio inexistente.
- Edición que conserva identificadores propios o contradice las fechas del historial.
- Imagen ausente en la computadora actual, archivo inválido y eliminación bloqueada.
- Repetir los datos de prueba sin duplicar ni sobrescribir equipos editados.
- Estructura ausente: informar la dependencia antes de insertar parcialmente.

## Requirements

### Functional Requirements

- **FR-001**: Fase 1 DEBE incluir instalación reproducible, estructura completa, relaciones, al menos 15 equipos y 3 laboratorios, listado, ficha e imagen predeterminada.
- **FR-002**: Fase 2 DEBE agregar creación, edición, eliminación confirmada, búsqueda/filtros y mensajes claros.
- **FR-003**: El código DEBE seguir `LAB-0000` y ser único; la serie también DEBE ser única.
- **FR-004**: Costos DEBEN ser no negativos y adquisición NO DEBE ser futura.
- **FR-005**: Mantenimiento NO DEBE preceder a adquisición ni registrarse para equipos de baja.
- **FR-006**: Registrar un correctivo DEBE dejar el equipo en reparación como una sola operación.
- **FR-007**: Fotografías DEBEN ser JPG, PNG o WebP de hasta 2 MB; operaciones fallidas DEBEN conservar archivos existentes.
- **FR-008**: Reportes DEBEN cubrir distribución por laboratorio/estado, costo por equipo y ausencia de mantenimiento en el último año.
- **FR-009**: La documentación DEBE permitir instalación y reconocer aportes reales.
- **FR-010**: Preparar datos DEBE ser reproducible, ordenado y sin inserciones parciales ante fallos.

### Key Entities

- **Laboratorio**: nombre, ubicación y capacidad; agrupa equipos.
- **Equipo**: código, tipo, marca, modelo, serie, adquisición, costo, estado, laboratorio y fotografía.
- **Mantenimiento**: equipo, fecha, tipo, descripción, técnico y costo.

## Success Criteria

### Measurable Outcomes

- **SC-001**: Otra persona instala con el README y abre listado y ficha tras integrar Fase 1.
- **SC-002**: Datos de prueba con al menos 15 equipos, 3 laboratorios e historial coherente.
- **SC-003**: Todos los casos inválidos obligatorios se rechazan aun desactivando la validación del navegador.
- **SC-004**: Los tres reportes coinciden con datos de totales y fechas conocidos.
- **SC-005**: Historial con al menos 12 commits en 2 días para Fase 1 y 20 adicionales en 3 días para Fase 2, con mínimos por integrante.

## Assumptions

- Base compartida y datos ficticios; Harry coordina estructura y reinicios.
- Borrado restringido para laboratorios con equipos y equipos con historial; estos últimos pueden marcarse de baja.
- Catálogo precargado; sin CRUD adicional del catálogo, login, exportaciones ni hosting.
- Fechas acordadas: 5 y 12 de noviembre de 2026.
- El proyecto inventario-laboratorios ya existe en idk, con referencia rdwfytkqjlvturwfedbg. La conexión de Laravel se verificó con PHP 8.3 y SSL requerido; falta habilitar pdo_pgsql en el PHP del sistema e integrar las migraciones del negocio.
