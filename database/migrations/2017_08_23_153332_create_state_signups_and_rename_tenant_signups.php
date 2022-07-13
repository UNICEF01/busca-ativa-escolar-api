<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateStateSignupsAndRenameTenantSignups extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table("signups", function (Blueprint $table) {
        	$table->rename('tenant_signups');
        });

        Schema::create("state_signups", function (Blueprint $table) {
	        $table->uuid('id');
	        $table->primary('id');

	        $table->string('uf')->index();
	        $table->uuid('user_id')->index()->nullable();
	        $table->uuid('judged_by')->index()->nullable();

	        $table->boolean('is_approved')->default(false);

	        $table->ipAddress('ip_addr')->nullable();
	        $table->string('user_agent', 512)->nullable();

	        $table->json('data')->nullable();

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
