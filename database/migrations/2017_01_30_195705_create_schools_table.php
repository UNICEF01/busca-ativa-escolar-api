<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSchoolsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create("schools", function (Blueprint $table) {
	        $table->string('id');
	        $table->primary('id');

			$table->string('name')->nullable();
			$table->string('uf_id')->nullable();
			$table->string('uf')->index()->nullable();
			$table->string('region')->index()->nullable();
			$table->uuid('city_id')->index()->nullable();
			$table->string('city_name')->nullable();
			$table->string('city_ibge_id')->index()->nullable();
			$table->json('metadata')->nullable();

			$table->timestamps();
			$table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
