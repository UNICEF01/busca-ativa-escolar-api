<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSupportTicketsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create("support_tickets", function (Blueprint $table) {
        	$table->uuid('id');
        	$table->primary('id');

        	$table->uuid('user_id')->index()->nullable();
        	$table->uuid('tenant_id')->index()->nullable();

        	$table->string('name')->nullable();
        	$table->string('city_name')->nullable();
        	$table->string('email')->nullable();
        	$table->string('phone')->nullable();
        	$table->string('user_agent')->nullable();
        	$table->mediumText('message')->nullable();

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
