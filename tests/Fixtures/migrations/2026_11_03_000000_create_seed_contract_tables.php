<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Contrato exclusivo de pruebas; las migraciones reales pertenecen a Miguel.
 * No ejecutar este fixture sobre la base compartida de Supabase.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('laboratorios', function (Blueprint $table): void {
            $table->id();
            $table->string('nombre', 100)->unique();
            $table->string('ubicacion', 150);
            $table->integer('capacidad');
            $table->timestamps();
        });

        Schema::create('equipos', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('laboratorio_id')->index()->constrained()->restrictOnDelete();
            $table->string('codigo_inventario', 8)->unique();
            $table->enum('tipo', ['computadora', 'proyector', 'impresora', 'switch']);
            $table->string('marca', 100);
            $table->string('modelo', 100);
            $table->string('numero_serie', 100)->unique();
            $table->date('fecha_adquisicion');
            $table->decimal('costo', 10, 2);
            $table->enum('estado', ['operativo', 'en_reparacion', 'de_baja'])->default('operativo');
            $table->string('imagen')->nullable();
            $table->timestamps();
        });

        Schema::create('mantenimientos', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('equipo_id')->index()->constrained()->restrictOnDelete();
            $table->date('fecha');
            $table->enum('tipo', ['preventivo', 'correctivo']);
            $table->text('descripcion');
            $table->string('tecnico_responsable', 150);
            $table->decimal('costo', 10, 2);
            $table->timestamps();
        });

        if (DB::getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE laboratorios ADD CONSTRAINT capacidad_positiva CHECK (capacidad > 0)');
            DB::statement('ALTER TABLE equipos ADD CONSTRAINT costo_equipo_no_negativo CHECK (costo >= 0)');
            DB::statement('ALTER TABLE mantenimientos ADD CONSTRAINT costo_mantenimiento_no_negativo CHECK (costo >= 0)');
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('mantenimientos');
        Schema::dropIfExists('equipos');
        Schema::dropIfExists('laboratorios');
    }
};
