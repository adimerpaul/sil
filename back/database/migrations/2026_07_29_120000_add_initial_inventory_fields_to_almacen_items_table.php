<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('almacen_items', function (Blueprint $table) {
            $table->decimal('precio_unitario_inicial', 14, 4)->default(0)->after('precio_unitario');
            $table->integer('saldo_inicial')->default(0)->after('precio_unitario_inicial');
            $table->integer('entradas_inicial')->default(0)->after('saldo_inicial');
            $table->integer('salidas_inicial')->default(0)->after('entradas_inicial');
            $table->integer('saldo_final_inicial')->default(0)->after('salidas_inicial');
        });

        if (! Schema::hasTable('compras_mayo')) {
            return;
        }

        // Un producto puede estar vinculado a varias filas del inventario de mayo.
        // Las cantidades se consolidan y el precio conserva el valor ponderado por
        // las unidades manejadas (saldo inicial + entradas).
        DB::statement('
            UPDATE almacen_items ai
            JOIN (
                SELECT
                    almacen_item_id,
                    CASE
                        WHEN SUM(saldo_inicial + entradas) > 0 THEN
                            SUM(precio_unitario * (saldo_inicial + entradas))
                            / SUM(saldo_inicial + entradas)
                        ELSE MAX(precio_unitario)
                    END AS precio_unitario_inicial,
                    SUM(saldo_inicial) AS saldo_inicial,
                    SUM(entradas) AS entradas_inicial,
                    SUM(salidas) AS salidas_inicial,
                    SUM(saldo_final) AS saldo_final_inicial
                FROM compras_mayo
                WHERE almacen_item_id IS NOT NULL
                GROUP BY almacen_item_id
            ) cm ON cm.almacen_item_id = ai.id
            SET
                ai.precio_unitario_inicial = cm.precio_unitario_inicial,
                ai.saldo_inicial = cm.saldo_inicial,
                ai.entradas_inicial = cm.entradas_inicial,
                ai.salidas_inicial = cm.salidas_inicial,
                ai.saldo_final_inicial = cm.saldo_final_inicial,
                ai.updated_at = NOW()
        ');
    }

    public function down(): void
    {
        Schema::table('almacen_items', function (Blueprint $table) {
            $table->dropColumn([
                'precio_unitario_inicial',
                'saldo_inicial',
                'entradas_inicial',
                'salidas_inicial',
                'saldo_final_inicial',
            ]);
        });
    }
};
