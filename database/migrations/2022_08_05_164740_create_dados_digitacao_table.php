<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDadosDigitacaoTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('mysql_local')->create('dados_digitacao', function (Blueprint $table) {
            $table->id();
            $table->string('phone', 20)->index();
            $table->string('cpf', 15)->nullable()->index();
            $table->string('nb', 15)->nullable()->index();
            $table->decimal('valor_limite', $precision = 8, $scale = 2)->nullable();
            $table->decimal('valor_margem', $precision = 8, $scale = 2)->nullable();
            $table->string('link_assinatura', 200)->nullable();
            $table->tinyInteger('confirmado')->default(0)->index();
            $table->tinyInteger('digitado')->default(0)->index();
            $table->timestamps();
            $table->unique(['cpf','nb']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('dados_digitacao');
    }
}
