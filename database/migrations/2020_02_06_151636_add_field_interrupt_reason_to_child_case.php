<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Migrations\Migration;


class AddFieldInterruptReasonToChildCase extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('children_cases', function($table) {
            $table->string('interrupt_reason')->after('cancel_reason')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('children_cases', function($table) {
            $table->dropColumn('interrupt_reason');
        });
    }
}
