# Tasks: Inventario de laboratorios

Documentos: [spec.md](spec.md), [plan.md](plan.md), [contrato de datos](data-model.md) y [contrato web](contracts/web.md).

Los identificadores H/M/D/A-F1/F2 corresponden a las guías individuales acordadas. `[P]` indica trabajo paralelo sin conflicto de archivos una vez cumplidas sus dependencias. Una casilla marcada acredita solo la parte descrita, no la fase completa.

## Phase 1: Setup — Harry

- [x] T001 Harry / H-F1-01: crear Laravel 13 y configurar PHP 8.3, zona horaria, sesión/caché local y ejemplo PostgreSQL/SSL en `composer.json`, `config/app.php` y `.env.example`.
- [ ] T002 Harry / H-F1-01: terminar la habilitación de pdo_pgsql en el PHP local y revisar el interruptor Data API del panel. Proyecto Supabase y usuario Laravel ya creados; conexión Session pooler comprobada desde Laravel con PHP 8.3 y SSL requerido. Credencial guardada únicamente en `.env`.
- [ ] T003 Harry / H-F1-01: confirmar usuarios y colaboradores de GitHub, incluida la docente, y completar la tabla de integrantes en `README.md`.
- [x] T004 Harry / H-F1-03: inicializar Spec Kit y registrar constitución, especificación, plan y reparto en `.specify/memory/constitution.md` y `specs/001-inventario-laboratorios/`.

## Phase 2: Foundational — estructura definitiva de Fase 1

- [ ] T005 Miguel / M-F1-01: crear las tres migraciones en `database/migrations/`, con FK indexadas/restrictOnDelete, nombre de laboratorio único, código string(8) único, serie string(100) única, costos decimal(10,2) no negativos, capacidad positiva e imagen como único campo nullable del negocio; respetar `data-model.md`.
- [ ] T006 Miguel / M-F1-02: implementar relaciones, fillable y casts en `app/Models/Laboratorio.php` y `app/Models/Mantenimiento.php`.
- [ ] T007 David / D-F1-01: implementar Equipo con laboratorio(), mantenimientos(), fillable y casts date/decimal:2 en `app/Models/Equipo.php`.
- [x] T008 Harry / H-F1-02: implementar factory y seeders transaccionales en `database/factories/EquipoFactory.php` y `database/seeders/`; verificar 3 laboratorios, 15 equipos, historial coherente y repetición sin sobrescrituras mediante `tests/Feature/SeedersTest.php` y PostgreSQL aislado.
- [ ] T009 Harry / H-F1-02: repetir `migrate --seed` y la reconstrucción coordinada sobre las migraciones definitivas de `database/migrations/`, después de T002 y T005; no sustituirlas por fixtures.
- [ ] T010 Miguel / M-F1-03: validar las restricciones reales y actualizar diagrama/diccionario en `README.md` comparándolo con `database/migrations/`.

**Checkpoint:** las semillas de Harry están probadas contra el contrato; la estructura de entrega sigue pendiente hasta completar T005–T010.

## Phase 3: US1 — Consultar inventario, Fase 1

**Meta:** listado de dos páginas y ficha con laboratorio e historial. **Prueba independiente:** instalar desde clon limpio, sembrar datos y navegar.

- [ ] T011 [US1] David / D-F1-02: registrar Route::resource y consultar equipos con laboratorio precargado y paginate(10) en `routes/web.php` y `app/Http/Controllers/EquipoController.php`.
- [ ] T012 [US1] David / D-F1-03: implementar show con route model binding, 404 e historial ordenado en `app/Http/Controllers/EquipoController.php`.
- [ ] T013 [P] [US1] Alfredo / A-F1-01: crear layout común y CSS propio en `resources/views/layouts/app.blade.php` y `public/css/estilos.css`; puede avanzar mientras David construye consultas.
- [ ] T014 [US1] Alfredo / A-F1-02: conectar miniaturas, estado vacío y paginación en `resources/views/equipos/index.blade.php`; requiere T011 y T013.
- [ ] T015 [US1] Alfredo / A-F1-03: conectar ficha, imagen predeterminada e inicio básico en `resources/views/equipos/show.blade.php` y `resources/views/panel/index.blade.php`; requiere T012 y T013.
- [x] T016 [US1] Harry / H-F1-03: documentar instalación, estado real, esquema acordado y reparto en `README.md`; comprobar arranque inicial y semillas aisladas con `tests/Feature/ApplicationTest.php` y `tests/Feature/SeedersTest.php`.
- [ ] T017 [US1] Harry / H-F1-03: ensayar la instalación completa desde otro clon y verificar listado/ficha, etiquetas CONCEPTO y mínimo de commits/autores/días; registrar resultado en `README.md` antes de crear v1.0.

## Phase 4: US2 — Administrar equipos, Fase 2

**Meta:** CRUD, búsqueda e imágenes. **Prueba independiente:** crear, editar, filtrar, borrar un equipo sin historial y rechazar datos inválidos con novalidate.

- [ ] T018 [US2] David / D-F2-01: implementar reglas de código LAB-0000, serie única, datos obligatorios, costo no negativo, adquisición no futura e imagen opcional JPG/PNG/WebP hasta 2048 KB en `app/Http/Requests/StoreEquipoRequest.php` y `UpdateEquipoRequest.php`.
- [ ] T019 [US2] David / D-F2-02: implementar create/store con datos validados y mensaje exito en `app/Http/Controllers/EquipoController.php`.
- [ ] T020 [US2] David / D-F2-03: implementar edit/update y bloquear adquisición posterior al primer mantenimiento, coordinando la transacción con Miguel, en `app/Http/Controllers/EquipoController.php`.
- [ ] T021 [US2] David / D-F2-04: implementar DELETE, mensaje por historial y conservación del registro/archivo ante restricción en `app/Http/Controllers/EquipoController.php`.
- [ ] T022 [US2] David / D-F2-05: agregar q/laboratorio_id/estado, OR agrupados y withQueryString; verificar CRUD y filtros en `app/Http/Controllers/EquipoController.php` y `tests/Feature/EquipoTest.php`.
- [ ] T023 [P] [US2] Alfredo / A-F2-01: crear formulario compartido y vistas create/edit con CSRF, PUT y multipart en `resources/views/equipos/_form.blade.php`, `create.blade.php` y `edit.blade.php`; coordinar campos con David.
- [ ] T024 [US2] Alfredo / A-F2-02: conservar old(), selecciones, errores por campo y mensajes flash en `resources/views/equipos/_form.blade.php` y `resources/views/layouts/app.blade.php`.
- [ ] T025 [US2] Alfredo / A-F2-03: conectar confirmación DELETE, filtros y estados vacíos en `resources/views/equipos/index.blade.php`; requiere T021 y T022.
- [ ] T026 [US2] Harry / H-F2-01: integrar carga y ruta relativa de fotografías con David en `app/Http/Controllers/EquipoController.php`; ventana exclusiva de edición del archivo.
- [ ] T027 [US2] Harry / H-F2-02: integrar reemplazo, compensación ante error y borrado posterior a éxito en `app/Http/Controllers/EquipoController.php`; no borrar imagen si se bloquea la operación.

## Phase 5: US3 — Mantenimientos, Fase 2

**Meta:** registrar/listar con fechas y estados correctos. **Prueba independiente:** aceptar preventivo/correctivo y rechazar fecha anterior o equipo de baja.

- [ ] T028 [US3] Miguel / M-F2-01: validar equipo existente, fecha igual o posterior a adquisición, tipo preventivo/correctivo, descripción/técnico obligatorios y costo no negativo en `app/Http/Requests/StoreMantenimientoRequest.php`.
- [ ] T029 [US3] Miguel / M-F2-02: implementar index/create/store, eager loading y mensajes; entregar rutas a David en `app/Http/Controllers/MantenimientoController.php` y `routes/web.php`.
- [ ] T030 [US3] Miguel / M-F2-03: volver a comprobar adquisición y estado bajo transacción/bloqueo de equipo en `app/Http/Controllers/MantenimientoController.php`.
- [ ] T031 [US3] Miguel / M-F2-04: insertar correctivo y cambiar a en_reparacion en la misma transacción, sin cierre automático, en `app/Http/Controllers/MantenimientoController.php`.
- [ ] T032 [US3] Miguel / M-F2-05: comprobar reglas, rollback y contrato con vistas en `tests/Feature/MantenimientoTest.php`; documentarlas en `README.md`.
- [ ] T033 [P] [US3] Alfredo / A-F2-04: construir listado/formulario con nombres acordados, errores y old() en `resources/views/mantenimientos/index.blade.php` y `create.blade.php`; puede avanzar después de fijar el contrato con Miguel.

## Phase 6: US4 — Reportes, Fase 2

**Meta:** tres resultados del PDF. **Prueba independiente:** comparar agregados y límite anual contra datos conocidos.

- [ ] T034 [US4] Harry / H-F2-03: implementar distribución laboratorio/estado, suma por equipo y whereDoesntHave de mantenimientos desde hoy menos un año, incluyendo nunca mantenidos, en `app/Http/Controllers/PanelController.php`.
- [ ] T035 [US4] Harry / H-F2-04: crear reporte e integrar indicadores de inicio con Alfredo en `resources/views/reportes/index.blade.php` y `resources/views/panel/index.blade.php`; documentar/crear fixtures de fechas límite en `tests/Feature/ReporteTest.php`.

## Final Phase: Calidad y entrega del equipo

- [ ] T036 Alfredo / A-F2-05: revisar 360/768/1280 px, teclado y pantallas con errores; guardar capturas en `docs/capturas/` y enlazarlas desde `README.md`.
- [ ] T037 Harry / H-F2-05: verificar imágenes con Storage::fake, reportes e integración en `tests/Feature/ImagenEquipoTest.php` y `tests/Feature/ReporteTest.php`; comprobar clon, documentación y criterios antes de v2.0.

## Dependencies & Execution Order

T001 permite iniciar todos los trabajos locales. T002/T005/T006/T007 permiten comprobar la integración definitiva y T009. T011/T012 y T013 permiten T014/T015; T017 cierra Fase 1 solo después de esos requisitos y la evidencia Git. Fase 2 continúa sobre v1.0; conserva la estructura y usa nuevas migraciones si cambia el esquema.

Las pruebas ordinarias siguen aisladas. Si se agregan pruebas contra PostgreSQL, deben apuntar a una base exclusiva y nunca usar RefreshDatabase/DatabaseMigrations sobre Supabase compartido.

## Parallel Examples

- US1: Alfredo puede construir layout mientras David prepara consultas y Miguel termina modelos, porque editan archivos distintos.
- US2: Alfredo puede preparar formulario mientras David implementa requests; la integración de imágenes de Harry espera una ventana acordada sobre EquipoController.
- US3: Alfredo prepara vistas con el contrato fijado mientras Miguel implementa reglas y controlador.
- US4: Harry prepara consultas y vista propia; coordina con Alfredo el cambio del inicio y estilos.

## Implementation Strategy

Priorizar US1 y la aceptación completa de v1.0 antes de extras. Integrar después CRUD, mantenimientos y reportes por incrementos probados. Cada integrante conserva sus commits, sus pruebas y la documentación de su aporte. Ninguna prueba con fixtures sustituye la demostración sobre las migraciones reales.
