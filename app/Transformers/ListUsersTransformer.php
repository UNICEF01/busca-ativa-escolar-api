<?php
/**
 * Created by PhpStorm.
 * User: manoelfilho
 * Date: 2018-12-06
 * Time: 00:00
 *
 * Class to transform a collection of users. Return only especif fields of Model User
 *
 */

namespace BuscaAtivaEscolar\Transformers;


use Illuminate\Database\Eloquent\Relations\HasMany;
use League\Fractal\TransformerAbstract;

class ListUsersTransformer extends TransformerAbstract
{

    public function transform(HasMany $users) {

        $list_users = [];
        foreach ($users->getResults() as $user){
            array_push($list_users, ['name' => $user->name, 'work_phone' => $user->work_phone, 'email' => $user->email, 'type' => $user->type, 'created_at' => $user->created_at->format('d/m/Y'), 'deleted_at' => $user->deleted_at ? $user->deleted_at->format('d/m/Y') : null] );
        }

        return $list_users;

    }
}