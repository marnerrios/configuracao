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
        Schema::create('whatsapp_api', function (Blueprint $table) {
            $table->id();
            $table->string('phone', 20)->unique();
            $table->string('chatName', 50)->index();
            $table->string('tipoMensagem', 50)->index();
            $table->tinyInteger('respondido')->default(0)->index();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('whatsapp_api');
    }
}
