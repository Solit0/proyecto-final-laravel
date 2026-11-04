# Comprobación rápida

## Base de Harry disponible hoy

Seguir [instalación del README](../../README.md). Configurar .env y generar APP_KEY. Se puede iniciar Laravel y abrir / y /up sin Supabase. Por ahora la raíz es la bienvenida del framework.

```sh
php artisan test --compact
```

Requiere pdo_sqlite; las pruebas fuerzan memoria y no usan la base compartida. Resultado esperado: arranque correcto y pruebas de semillas pasadas.

## Integración definitiva pendiente

1. Miguel agrega migraciones y modelos; David agrega Equipo, consultas y rutas; Alfredo agrega layout, listado y ficha.
2. Harry habilita pdo_pgsql local, utiliza el .env privado del proyecto Supabase ya creado y confirma colaboradores; puede comprobar conexión con `php artisan db:show --database=pgsql --json --no-interaction`.
3. Harry coordina `php artisan migrate --seed --no-interaction`.
4. Un compañero instala con el README y comprueba dos páginas del listado, ficha, laboratorio e historial.
5. Solo sobre datos de prueba acordados, Harry demuestra `php artisan migrate:fresh --seed --no-interaction` con las migraciones definitivas.
6. Revisar mínimos de commits, autores y días reales antes de etiquetar v1.0.

No ejecutar tests/Fixtures/migrations contra Supabase; una prueba sobre esas tablas no completa el punto 5.

## Fase 2

Comprobar los escenarios de [spec.md](spec.md): CRUD, novalidate, mantenimiento inválido/correctivo, imágenes y los tres reportes. Registrar capturas reales y pruebas antes de etiquetar v2.0.
