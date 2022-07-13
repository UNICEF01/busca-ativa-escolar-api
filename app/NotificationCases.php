<?php

namespace BuscaAtivaEscolar;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use BuscaAtivaEscolar\Traits\Data\IndexedByUUID;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property string $id
 * @property Tenant $tenant_id
 * @property User $user_id
 * @property ChildCase  $children_case_id
 * @property ChildCase  $case_tree_id
 * @property string $notification
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property \Carbon\Carbon $deleted_at
 */

class NotificationCases extends Model
{
    use HasFactory;
    use IndexedByUUID;
    use SoftDeletes;

    protected $table = "notification_cases";

    protected $fillable = [
        'id',
        'tenant_id',
        'user_id',
        'children_case_id',
        'notification',
        'case_tree_id',
        'users_tree_id',
        'solved',
        'deleted_at'
    ];

    protected $hidden = [
        'created_at', 'update_at'
    ];

    public function tenant()
	{
		return $this->hasOne('BuscaAtivaEscolar\Tenant', 'id', 'tenant_id');
	}

    public function user()
	{
		return $this->hasOne('BuscaAtivaEscolar\User', 'id', 'user_id');
	}

    public function case()
	{
		return $this->hasOne('BuscaAtivaEscolar\ChildCase', 'id', 'children_case_id');
	}
}
