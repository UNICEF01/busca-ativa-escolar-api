<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateImportJobsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create("import_jobs", function (Blueprint $table) {
        	$table->increments('id');

        	$table->string('type')->index();
        	$table->string('status')->default('pending');

        	$table->string('path')->nullable();

        	$table->integer('offset')->default(0);
        	$table->integer('total_records')->default(0);

        	$table->json('errors')->nullable();

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
