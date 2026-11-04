# Implementation Plan: Inventario de laboratorios

**Branch**: `chore/harry-fase-1` | **Date**: 2026-11-03 | **Spec**: [spec.md](spec.md)

## Summary

Implementar el caso 4 en un único proyecto Laravel 13 con Blade, CSS propio y Supabase PostgreSQL. Esta entrega implementa únicamente la Fase 1 de Harry: entorno, configuración, semillas, pruebas y documentación. Las tareas de Miguel, David y Alfredo permanecen abiertas.

## Technical Context

- **Language/Version**: PHP 8.3+; dependencias resueltas con `config.platform.php=8.3.0`.
- **Primary Dependencies**: Laravel 13, Faker, PHPUnit y Pint. Laravel Boost se instaló como dependencia de desarrollo siguiendo las instrucciones iniciales del scaffold.
- **Storage**: Supabase PostgreSQL vía Session pooler, SSL obligatorio; fotografías locales en disco public durante Fase 2.
- **Testing**: PHPUnit con SQLite en memoria forzado; comprobación adicional de seeders contra PostgreSQL 16 aislado.
- **Target Platform**: Computadoras del equipo, `php artisan serve`; sin hosting ni servicios adicionales para ejecutar la aplicación.
- **Project Type**: Aplicación MVC con renderizado Blade, sin API separada.
- **Performance Goals**: Paginación de 10 equipos y relaciones precargadas; sin consultas por fila en las vistas.
- **Constraints**: Base compartida; reinicios solo coordinados por Harry. Credenciales fuera de Git. Supabase ya está creado y la conexión de Laravel verificada; falta habilitar pdo_pgsql en el PHP del sistema.
- **Scale/Scope**: Tres tablas del negocio, al menos 15 equipos y 3 laboratorios; 4 integrantes y 2 entregas.

## Constitution Check

- Se conserva Laravel estándar y se omiten extras opcionales.
- No se declara terminada Fase 1 por completar únicamente la parte de Harry.
- Las fixtures viven fuera de las migraciones de entrega y nunca acreditan la estructura definitiva.
- Pruebas ordinarias aisladas; sin borrado de Supabase ni credenciales en Git.
- Los commits y el reparto reflejan autoría real; no se fabrican fechas.

Revisión previa y posterior al diseño: sin excepciones a estos acuerdos.

## Project Structure

### Documentation

`spec.md`, `research.md`, `data-model.md`, `contracts/web.md`, `quickstart.md` y `tasks.md` describen el trabajo del equipo. `.specify/memory/constitution.md` contiene sus reglas y README.md documenta la instalación oficial.

### Source Code

- `app/Models`, `app/Http/Controllers` y `app/Http/Requests`: modelos, recursos y validación a cargo de Miguel y David.
- `database/migrations`: estructura definitiva a cargo de Miguel.
- `database/factories` y `database/seeders`: datos de Harry.
- `resources/views` y `public/css`: interfaz de Alfredo; reporte final de Harry.
- `tests/Feature`: arranque y semillas; `tests/Fixtures/migrations` contiene solo el contrato de prueba.

**Structure Decision**: Una sola aplicación Laravel; sin repositorios, capas de servicio o frontend separados.

## Implementation Sequence

1. Harry prepara proyecto, configuración, factory/seeders y README; los verifica sin depender de los módulos pendientes.
2. Miguel agrega las migraciones reales y sus modelos; David incorpora Equipo y consultas; Alfredo integra layout, listado y ficha.
3. Harry completa la habilitación del controlador PostgreSQL local, coordina migraciones/semillas sobre el proyecto Supabase ya creado y verifica el clon completo. Solo entonces corresponde evaluar v1.0.
4. En Fase 2 se implementan CRUD, imágenes, mantenimientos y reportes con los contratos acordados. Cambios de esquema posteriores requieren nuevas migraciones.

## Complexity Tracking

No se agregan abstracciones propias. Las pruebas usan una fixture explícita porque la estructura definitiva pertenece a otro integrante y todavía no está disponible. Se debe repetir la integración sobre las migraciones reales cuando lleguen.
