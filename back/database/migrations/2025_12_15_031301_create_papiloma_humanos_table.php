<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('papiloma_humanos', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('solicitude_id');

            // Resultados PCR
            $table->string('hpv_alto_riesgo')->nullable(); // NO DETECTADO / DETECTADO
            $table->string('hpv_16')->nullable();
            $table->string('hpv_18')->nullable();
            $table->string('hpv_45')->nullable();
            $table->string('code', 100)->nullable();

            $table->text('observaciones')->nullable();

            $table->softDeletes();
            $table->timestamps();

            $table->foreign('solicitude_id')
                ->references('id')
                ->on('solicitudes')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('papiloma_humanos');
    }
};
