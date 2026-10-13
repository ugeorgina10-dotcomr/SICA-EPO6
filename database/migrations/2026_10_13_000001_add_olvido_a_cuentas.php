<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Marca de "olvidó su contraseña": mientras tenga fecha, la cuenta no puede entrar.
     * Solo Control Escolar la quita (al generar una contraseña nueva o al desbloquear).
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->timestamp('olvido_at')->nullable()->after('password');
        });

        Schema::table('orientadores', function (Blueprint $table) {
            $table->timestamp('olvido_at')->nullable()->after('password');
        });
    }

    public function down(): void
    {
        Schema::table('orientadores', function (Blueprint $table) {
            $table->dropColumn('olvido_at');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('olvido_at');
        });
    }
};
