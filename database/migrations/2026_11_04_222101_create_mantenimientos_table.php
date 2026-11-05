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
        Schema::create('mantenimientos', function (Blueprint $table): void {
            $table->id(); // [MIGRACION]
            // [RELACION] PostgreSQL no indexa automáticamente una FK; el índice es explícito.
            $table->foreignId('equipo_id')->index()->constrained()->restrictOnDelete();
            $table->date('fecha'); // [MIGRACION]
            $table->enum('tipo', ['preventivo', 'correctivo']); // [MIGRACION]
            $table->text('descripcion'); // [MIGRACION]
            $table->string('tecnico_responsable', 150); // [MIGRACION]
            $table->decimal('costo', 10, 2); // [MIGRACION]
            $table->timestamps(); // [MIGRACION]
        });

        if (DB::getDriverName() === 'pgsql') {
            // PostgreSQL no aplica unsigned: la restricción CHECK impide costos negativos.
            DB::statement('ALTER TABLE mantenimientos ADD CONSTRAINT mantenimientos_costo_no_negativo CHECK (costo >= 0)');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mantenimientos');
    }
};
