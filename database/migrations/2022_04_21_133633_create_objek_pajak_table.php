<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateObjekPajakTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('objek_pajak', function (Blueprint $table) {
            $table->id();
            $table->string('npwpd');
            $table->char('kd_objek_pajak')->unique();
            $table->string('nama_wp');
            $table->string('objek_pajak');
            $table->string('lokasi_objek');
            $table->string('jns_reklame');
            $table->string('kecamatan');
            $table->smallInteger('panjang')->default(0);
            $table->smallInteger('lebar')->default(0);
            $table->smallInteger('tinggi')->default(0);
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
        Schema::dropIfExists('objek_pajak');
    }
}
