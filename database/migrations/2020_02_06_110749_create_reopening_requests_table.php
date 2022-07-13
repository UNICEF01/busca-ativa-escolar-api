<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateReopeningRequestsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('reopening_requests', function (Blueprint $table) {
            $table->uuid('id');
            $table->char('requester_id', 36);
            $table->char('recipient_id', 36)->nullable();
            $table->char('child_id', 36);
            $table->char('status', 50);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('reopening_requests');
    }
}
