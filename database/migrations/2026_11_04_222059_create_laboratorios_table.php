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
        Schema::create('laboratorios', function (Blueprint $table): void {
            $table->id(); // [MIGRACION]
            $table->string('nombre', 100)->unique(); // [MIGRACION]
            $table->string('ubicacion', 150); // [MIGRACION]
            $table->integer('capacidad'); // [MIGRACION]
            $table->timestamps(); // [MIGRACION]
        });

        if (DB::getDriverName() === 'pgsql') {
            // PostgreSQL no aplica unsigned: la restricción CHECK protege la capacidad.
            DB::statement('ALTER TABLE laboratorios ADD CONSTRAINT laboratorios_capacidad_positiva CHECK (capacidad > 0)');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('laboratorios');
    }
};
