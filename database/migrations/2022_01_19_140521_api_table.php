<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ApiTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('api_table', function (Blueprint $table) {
            $table->increments('id');
            $table->string('token');
        });

        DB::table('api_table')->insert([
            'token' => password_hash('alliance_tech_system', PASSWORD_DEFAULT)
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('api_table');
    }
}
