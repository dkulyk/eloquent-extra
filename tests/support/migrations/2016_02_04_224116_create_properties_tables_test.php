<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreatePropertiesTablesTest extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(
            'fields',
            function (Blueprint $table) {
                $table->increments('id');
                $table->string('partner');
                $table->string('name');
                $table->string('type');
                $table->boolean('multiple');
                $table->string('default_value')->nullable();
                $table->timestamps();

                $table->unique(['entity', 'name']);
            }
        );
        Schema::create(
            'property_values_int',
            function (Blueprint $table) {
                $table->integer('field_id', false, true);
                $table->integer('entity_id', false, true);
                $table->bigInteger('value');

                $table->index(['field_id', 'entity_id']);
                $table->foreign('field_id')
                    ->references('id')->on('fields')
                    ->onDelete('cascade');
            }
        );
        Schema::create(
            'property_values_string',
            function (Blueprint $table) {
                $table->integer('field_id', false, true);
                $table->integer('entity_id', false, true);
                $table->string('value');

                $table->index(['field_id', 'entity_id']);
                $table->foreign('field_id')
                    ->references('id')->on('fields')
                    ->onDelete('cascade');
            }
        );
        Schema::create(
            'property_values_date',
            function (Blueprint $table) {
                $table->integer('field_id', false, true);
                $table->integer('entity_id', false, true);
                $table->date('value');

                $table->index(['field_id', 'entity_id']);
                $table->foreign('field_id')
                    ->references('id')->on('fields')
                    ->onDelete('cascade');
            }
        );
        Schema::create(
            'property_values_datetime',
            function (Blueprint $table) {
                $table->integer('field_id', false, true);
                $table->integer('entity_id', false, true);
                $table->dateTime('value');

                $table->index(['field_id', 'entity_id']);
                $table->foreign('field_id')
                    ->references('id')->on('fields')
                    ->onDelete('cascade');
            }
        );
        Schema::create(
            'property_values_text',
            function (Blueprint $table) {
                $table->integer('field_id', false, true);
                $table->integer('entity_id', false, true);
                $table->text('value');

                $table->index(['field_id', 'entity_id']);
                $table->foreign('field_id')
                    ->references('id')->on('fields')
                    ->onDelete('cascade');
            }
        );
        Schema::create(
            'property_values_float',
            function (Blueprint $table) {
                $table->integer('field_id', false, true);
                $table->integer('entity_id', false, true);
                $table->text('value');

                $table->index(['field_id', 'entity_id']);
                $table->foreign('field_id')
                    ->references('id')->on('fields')
                    ->onDelete('cascade');
            }
        );
        Schema::create(
            'property_values_bool',
            function (Blueprint $table) {
                $table->integer('field_id', false, true);
                $table->integer('entity_id', false, true);
                $table->boolean('value');

                $table->index(['field_id', 'entity_id']);
                $table->foreign('field_id')
                    ->references('id')->on('fields')
                    ->onDelete('cascade');
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
        Schema::drop('property_values');
        Schema::drop('properties');
    }
}
