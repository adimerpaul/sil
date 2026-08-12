<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('hematologias', function (Blueprint $table) {
            $table->date('fecha_muestreo')->nullable()->after('solicitude_id');
        });
    }

    public function down(): void
    {
        Schema::table('hematologias', function (Blueprint $table) {
            $table->dropColumn('fecha_muestreo');
        });
    }
};
