<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSignupsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create("signups", function (Blueprint $table) {
        	$table->uuid('id');
        	$table->primary('id');

        	$table->uuid('city_id')->index();
        	$table->uuid('tenant_id')->nullable();

        	$table->boolean('is_approved')->default(false);
        	$table->boolean('is_provisioned')->default(false);

        	$table->ipAddress('ip_addr')->nullable();
        	$table->string('user_agent', 512)->nullable();

        	$table->json('data')->nullable();

        	$table->timestamps();
        	$table->softDeletes();
        });

        Schema::table("tenants", function (Blueprint $table) {
        	$table->uuid('signup_id')->nullable()->after('city_id');
        	$table->boolean('is_setup')->default(false)->after('is_active');
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
