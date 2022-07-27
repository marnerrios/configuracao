<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWhatsappApiTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('mysql_local')->create('whatsapp_api', function (Blueprint $table) {
            $table->id();
            $table->string('phone', 20)->index();
            $table->string('chatName', 50)->index();
            $table->string('tipoMensagem', 50)->index();
            $table->string('campanha', 50)->index();
            $table->tinyInteger('respondido')->default(0)->index();
            $table->string('cpf', 15)->nullable()->index();
            $table->string('dados_conta', 100)->nullable();
            $table->decimal('valor_limite', $precision = 8, $scale = 2)->nullable();
            $table->string('email', 100)->nullable();
            $table->string('link_documento', 200)->nullable();
            $table->string('link_endereco', 200)->nullable();
            $table->string('link_selfie', 200)->nullable();
            $table->string('link_dados_conta', 200)->nullable();
            $table->timestamps();
            $table->unique(['phone','campanha']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection('mysql_local')->dropIfExists('whatsapp_api');
    }
}
