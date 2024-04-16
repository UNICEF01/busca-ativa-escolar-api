<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateNotificationCasesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('notification_cases', function (Blueprint $table) {
            $table->uuid('id');
            $table->uuid('tenant_id')->index();
            $table->uuid('user_id')->index();
            $table->uuid('comment_id')->index();
            $table->uuid('children_case_id')->index();
            $table->string('notification');
            $table->string('case_tree_id', 150);
            $table->string('users_tree_id', 150)->comment = 'ids dos grupos superiores que contenham coordenador/supervisor';
            $table->boolean('solved')->index()->default(false)->comment = 'flag para ver se a notificação foi resolvida';
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
        Schema::dropIfExists('notification_cases');
    }
}
