<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateGroupsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create("groups", function (Blueprint $table) {
        	$table->uuid('id');
        	$table->primary('id');

        	$table->uuid('tenant_id')->nullable()->index();
        	$table->string('name')->nullable();
        	$table->boolean('is_primary')->default(0)->index();

        	$table->timestamps();
        	$table->softDeletes();
        });

        Schema::table("users", function (Blueprint $table) {
        	$table->uuid('group_id')->after('city_id')->index()->nullable();
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
