<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTableHistoricalSignups extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create("historical_signups", function (Blueprint $table) {
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
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('historical_signups');
    }
}
