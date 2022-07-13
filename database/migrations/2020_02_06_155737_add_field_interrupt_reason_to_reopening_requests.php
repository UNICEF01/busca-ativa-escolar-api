<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Migrations\Migration;


class AddFieldInterruptReasonToReopeningRequests extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('reopening_requests', function($table) {
            $table->string('interrupt_reason')->after('status')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('reopening_requests', function($table) {
            $table->dropColumn('interrupt_reason');
        });
    }
}
