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
        Schema::create('registro_imagens', function (Blueprint $table) {
            $table->char('id', 36)->primary();

            $table->char('sobre_plantilla_id', 32);
            $table->foreign('sobre_plantilla_id')->references('id')->on('sobre_plantillas')->onDelete('cascade');
            $table->string('imagen');
            $table->text('title')->nullable();
            $table->string('tipo')->nullable();
            $table->integer('orden')->default(0);
            $table->integer('ancho')->nullable();
            $table->integer('alto')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('registro_imagens');
    }
};
