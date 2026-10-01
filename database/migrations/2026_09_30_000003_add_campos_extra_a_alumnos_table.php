<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('grupos', function (Blueprint $table) {
            $table->unsignedTinyInteger('grado')->nullable()->after('nombre'); // 1, 2 o 3
            $table->foreignId('orientador_id')->nullable()->after('turno')
                ->constrained('orientadores')->nullOnDelete();
            $table->time('hora_entrada')->nullable()->after('orientador_id');
            $table->time('hora_salida')->nullable()->after('hora_entrada');
            $table->longText('plantilla_frente')->nullable()->after('hora_salida');
            $table->longText('plantilla_reverso')->nullable()->after('plantilla_frente');
        });
    }

    public function down(): void
    {
        Schema::table('grupos', function (Blueprint $table) {
            $table->dropConstrainedForeignId('orientador_id');
            $table->dropColumn(['grado', 'hora_entrada', 'hora_salida', 'plantilla_frente', 'plantilla_reverso']);
        });
    }
};