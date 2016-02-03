<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateContactTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(
            'contacts',
            function (Blueprint $table) {
                $table->increments('id');
                $table->string('email', 64)->unique();
                $table->tinyInteger('status', false, true)->default(0);

                $table->timestamps();
                $table->softDeletes()->index();
            }
        );

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('contacts');
    }

}
