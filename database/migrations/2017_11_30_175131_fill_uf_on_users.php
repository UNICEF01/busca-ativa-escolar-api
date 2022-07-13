<?php

use BuscaAtivaEscolar\User;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class FillUfOnUsers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $users = User::with('tenant')->get();

        foreach($users as $user) {
        	if($user->uf) continue;
        	if(!$user->tenant) continue;
        	DB::table("users")->where('id', $user->id)->update(['uf' => $user->tenant->uf]);
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
