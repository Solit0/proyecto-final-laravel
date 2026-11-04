# Decisiones verificadas

## Compatibilidad de PHP

**Decisión:** resolver Composer con plataforma PHP 8.3.0 y versionar composer.lock.
**Razón:** el equipo puede usar PHP 8.3 aunque esta computadora use 8.5; no bloquear a los compañeros con dependencias de una versión mayor.
**Alternativa descartada:** resolver únicamente según el PHP local. Las restricciones PHP de los paquetes bloqueados aceptan 8.3 y las pruebas se ejecutaron realmente con PHP 8.3.

## Supabase como PostgreSQL

**Decisión:** Session pooler, host/usuario copiados de Connect, puerto 5432 y SSL obligatorio.
**Razón:** conexión IPv4 y compatibilidad con conexiones PDO habituales. Desactivar Data API porque no existe un cliente REST/GraphQL.
**Alternativa descartada:** Transaction pooler 6543, innecesario para este proceso local y con diferencias de prepared statements.
**Fuente:** [conexiones Supabase](https://supabase.com/docs/guides/database/connecting-to-postgres), [Data API](https://supabase.com/docs/guides/api/securing-your-api).

## Dependencias del reparto

**Decisión:** implementar semillas con Query Builder y atributos de EquipoFactory; probar contra una fixture fuera de las migraciones reales.
**Razón:** las migraciones de Miguel y Equipo de David todavía no existen. Así se puede verificar Harry sin implementar o adjudicar el trabajo de sus compañeros.
**Alternativa descartada:** copiar las fixtures a database/migrations y presentar la estructura como terminada. Se debe repetir la integración cuando lleguen las migraciones reales.

## Repetición de semillas

**Decisión:** códigos de ejemplo estables; conservar equipos existentes y evitar repetir el historial de ejemplo. Todo el proceso se ejecuta en una transacción.
**Razón:** una segunda ejecución no debe pisar los cambios de los compañeros ni dejar catálogos parciales ante un fallo.

## Pruebas

**Decisión:** pruebas ordinarias en SQLite en memoria, sin traits que reinicien la base compartida; una comprobación adicional contra PostgreSQL 16 aislado.
**Razón:** repetibilidad sin tocar Supabase. Las fixtures demuestran compatibilidad del contrato de semillas, no el esquema definitivo ni la conexión a la nube.

## Pendientes externos

Crear Supabase y configurar .env, habilitar pdo_pgsql en el PHP del sistema, confirmar colaboradores y usuarios, integrar migraciones/modelos/vistas y comprobar aportes en días distintos. Son tareas abiertas, no decisiones técnicas sin resolver.
