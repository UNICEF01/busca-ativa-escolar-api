<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateSchoolsTableAddCelTelEmail extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        \Schema::table('schools', function ($table){
            $table->string('school_cell_phone', 13)->nullable()->after('uf');
            $table->string('school_phone', 13)->nullable()->after('school_cell_phone');
            $table->string('school_email', 255)->nullable()->after('school_phone');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        \Schema::table('schools', function($table) {
            $table->dropColumn('school_cell_phone');
            $table->dropColumn('school_phone');
            $table->dropColumn('school_email');
        });
    }
}
