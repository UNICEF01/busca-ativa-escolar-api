<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddNewFieldsToReopeningRequestsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('reopening_requests', function($table) {
            $table->char('tenant_requester_id', 36)->after('interrupt_reason')->nullable();
            $table->char('tenant_recipient_id', 36)->after('tenant_requester_id')->nullable();
            $table->string('type_request')->after('tenant_recipient_id')->nullable();
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
            $table->dropColumn('tenant_requester_id');
            $table->dropColumn('tenant_recipient_id');
            $table->dropColumn('type_request');
        });
    }
}
