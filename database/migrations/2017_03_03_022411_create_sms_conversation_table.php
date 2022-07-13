<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSmsConversationTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create("sms_conversations", function (Blueprint $table) {
        	$table->uuid('id');
        	$table->primary('id');

        	$table->uuid('user_id')->index()->nullable();
        	$table->uuid('tenant_id')->index()->nullable();

        	$table->uuid('spawned_child_id')->nullable();

        	$table->string('phone_number')->nullable()->index();
        	$table->string('conversation_step')->nullable();

        	$table->json('received_messages')->nullable();
        	$table->json('metadata')->nullable();
        	$table->json('fields')->nullable();

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
