<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCommentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create("comments", function (Blueprint $table) {
        	$table->uuid('id');
        	$table->primary('id');

        	$table->uuid('tenant_id')->index()->nullable();

        	$table->string('content_type')->index();
        	$table->uuid('content_id')->index();

        	$table->uuid('author_id')->index()->nullable();

        	$table->mediumText('contents')->nullable();
        	$table->json('metadata')->nullable();

        	$table->timestamps();
        	$table->softDeletes();
        });

        Schema::create("attachments", function (Blueprint $table) {
        	$table->uuid('id');
        	$table->primary('id');

        	$table->uuid('tenant_id')->index()->nullable();

	        $table->string('content_type')->index();
	        $table->uuid('content_id')->index();

	        $table->uuid('uploader_id')->index()->nullable();

	        $table->string('mime_type')->nullable();
	        $table->string('uri')->nullable();
	        $table->string('location')->nullable();

	        $table->json('metadata')->nullable();

	        $table->timestamps();
	        $table->softDeletes();
        });

        Schema::create("activity_log", function (Blueprint $table) {
        	$table->uuid('id');
        	$table->primary('id');

        	$table->uuid('tenant_id')->index()->nullable();
        	$table->uuid('user_id')->index()->nullable();

	        $table->string('content_type')->index();
	        $table->uuid('content_id')->index();

	        $table->string('action')->index();
	        $table->json('parameters')->nullable();
	        $table->json('metadata')->nullable();

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
