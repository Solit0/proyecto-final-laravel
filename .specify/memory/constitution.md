# Inventario de laboratorios Constitution

## Core Principles

### I. La rúbrica define el alcance

El equipo DEBE implementar el caso 4 y conservar la continuidad entre ambas fases en un único repositorio. La aplicación DEBE ejecutarse localmente. Autenticación, exportaciones y despliegue quedan fuera del alcance obligatorio.

### II. La solución mínima debe estar completa

El código DEBE usar la estructura normal de Laravel, Eloquent o Query Builder, vistas Blade y CSS propio. Se DEBEN evitar capas y dependencias que no resuelvan un requisito concreto. Las tareas de otra persona se integran sin adjudicar su autoría.

### III. Integridad y validación en el servidor

La estructura del negocio DEBE crearse con migraciones y restricciones. Fase 2 DEBE validar con Form Requests, conservar valores y mostrar errores en español. Registro correctivo y cambio de estado DEBEN ser atómicos. Las reglas importantes DEBEN tener pruebas reproducibles.

### IV. Secretos y datos compartidos protegidos

Credenciales y archivos subidos NO DEBEN entrar en Git. Supabase DEBE usar SSL. Harry DEBE coordinar las migraciones y cualquier reinicio de la base compartida. Las pruebas ordinarias DEBEN usar SQLite en memoria y NO DEBEN limpiar Supabase.

### V. Evidencia antes de marcar una tarea terminada

Cada responsable DEBE comprobar su módulo y documentar resultados reales. Una fixture NO acredita una migración definitiva. Los commits DEBEN conservar autoría y fechas reales: mínimo 3 por integrante en Fase 1 y 5 nuevos en Fase 2, con los días de trabajo exigidos por la rúbrica.

## Restricciones técnicas

Laravel 13, PHP 8.3 o superior, Composer 2 y PostgreSQL de Supabase. Sesiones y caché en archivos, cola síncrona. Spec Kit es apoyo; el README es la documentación oficial. Presentaciones acordadas: 5 y 12 de noviembre de 2026.

## Flujo de desarrollo y revisión

Constitución → especificación → plan → tareas → implementación → verificación. Trabajar en ramas y revisar antes de integrar. Crear etiquetas v1.0 y v2.0 únicamente cuando la fase completa cumpla su aceptación. Después de v1.0, los cambios de esquema requieren migraciones nuevas.

## Governance

Registrar cambios de estos acuerdos en un commit y actualizar documentos afectados. Cambios incompatibles incrementan la versión mayor; principios nuevos, la menor; aclaraciones, el parche. Las instrucciones del usuario y la rúbrica prevalecen sobre esta constitución.

**Version**: 1.0.0 | **Ratified**: 2026-11-03 | **Last Amended**: 2026-11-03
