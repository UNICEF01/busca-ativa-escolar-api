<?php

use BuscaAtivaEscolar\Group;
use BuscaAtivaEscolar\Settings\GroupSettings;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class FixGroupSettingsForInepCause extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $groups = Group::all();

        foreach($groups as $group) {

        	$original = $group->getSettings();
        	$settings = new GroupSettings($group);
        	$settings->alerts = $original->alerts;

        	$group->setSettings($settings);
        }
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
