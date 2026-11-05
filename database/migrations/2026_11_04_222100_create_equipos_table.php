<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('equipos', function (Blueprint $table): void {
            $table->id(); // [MIGRACION]
            // [RELACION] PostgreSQL no indexa automáticamente una FK; el índice es explícito.
            $table->foreignId('laboratorio_id')->index()->constrained()->restrictOnDelete();
            $table->string('codigo_inventario', 8)->unique(); // [MIGRACION]
            $table->enum('tipo', ['computadora', 'proyector', 'impresora', 'switch']); // [MIGRACION]
            $table->string('marca', 100); // [MIGRACION]
            $table->string('modelo', 100); // [MIGRACION]
            $table->string('numero_serie', 100)->unique(); // [MIGRACION]
            $table->date('fecha_adquisicion'); // [MIGRACION]
            $table->decimal('costo', 10, 2); // [MIGRACION]
            $table->enum('estado', ['operativo', 'en_reparacion', 'de_baja'])->default('operativo'); // [MIGRACION]
            $table->string('imagen')->nullable(); // [MIGRACION] Único campo nullable del negocio.
            $table->timestamps(); // [MIGRACION]
        });

        if (DB::getDriverName() === 'pgsql') {
            // PostgreSQL no aplica unsigned: la restricción CHECK impide costos negativos.
            DB::statement('ALTER TABLE equipos ADD CONSTRAINT equipos_costo_no_negativo CHECK (costo >= 0)');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('equipos');
    }
};
