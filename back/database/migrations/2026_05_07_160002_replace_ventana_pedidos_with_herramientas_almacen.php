<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('ventana_pedidos');

        Schema::create('herramientas_almacen', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 100)->unique();
            $table->string('valor', 255)->nullable();
            $table->timestamps();
        });

        DB::table('herramientas_almacen')->insert([
            ['nombre' => 'fecha_inicio_pedido_almacen', 'valor' => null, 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'fecha_fin_pedido_almacen',    'valor' => null, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('herramientas_almacen');
    }
};
