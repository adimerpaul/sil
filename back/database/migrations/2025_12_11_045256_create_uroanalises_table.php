<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('uroanalisis', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('solicitude_id');

            // Cabecera
            $table->string('material_ensayo')->nullable(); // ORINA
            $table->string('metodo')->nullable();          // MANUAL/MICROSCOPICO/TIRA REACTIVA

            // EXAMEN FÍSICO
            $table->decimal('cantidad', 8, 2)->nullable(); // ml
            $table->string('color')->nullable();
            $table->string('olor')->nullable();
            $table->string('aspecto')->nullable();
            $table->string('reaccion')->nullable();        // pH 6.0 ácido, etc.
            $table->decimal('densidad', 5, 3)->nullable(); // 1.020
            $table->string('espuma')->nullable();
            $table->string('sedimento')->nullable();

            // EXAMEN MICROSCÓPICO (SEDIMENTO)
            $table->string('celulas_epiteliales')->nullable();
            $table->string('leucocitos')->nullable();
            $table->string('hematies')->nullable();
            $table->string('bacterias')->nullable();
            $table->string('filamento_mucoide')->nullable();
            $table->string('cilindros')->nullable();
            $table->string('celulas')->nullable();         // células varias
            $table->string('cristales')->nullable();

            // OTROS EXÁMENES
            $table->text('morfologia_eritrocitaria')->nullable();

            // EXAMEN QUÍMICO
            $table->string('proteinas')->nullable();
            $table->string('glucosa')->nullable();
            $table->string('sangre')->nullable();
            $table->string('cetonas')->nullable();
            $table->string('bilirrubina')->nullable();
            $table->string('urobilinogeno')->nullable();
            $table->string('nitritos')->nullable();

            // Observaciones generales
            $table->text('observaciones')->nullable();

            $table->foreign('solicitude_id')
                ->references('id')
                ->on('solicitudes')
                ->onDelete('cascade');

            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('uroanalisis');
    }
};
