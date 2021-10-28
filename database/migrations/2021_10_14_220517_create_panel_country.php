<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePanelCountry extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('panel_country', function (Blueprint $table) {
            $table->uuid('id');
            $table->primary('id');
            $table->integer("num_tenants");
            $table->integer("num_ufs");
            $table->integer("num_signups");
            $table->integer("num_pending_setup");
            $table->integer("num_alerts");
            $table->integer("num_pending_alerts");
            $table->integer("num_rejected_alerts");
            $table->integer("num_total_alerts");
            $table->integer("num_cases_in_progress");
            $table->integer("num_children_reinserted");
            $table->integer("num_pending_signups");
            $table->integer("num_pending_state_signups");
            $table->integer("num_children_in_school");
            $table->integer("num_children_in_observation");
            $table->integer("num_children_out_of_school");
            $table->integer("num_children_cancelled");
            $table->integer("num_children_transferred");
            $table->integer("num_children_interrupted");
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
        Schema::dropIfExists('panel_country');
    }
}
