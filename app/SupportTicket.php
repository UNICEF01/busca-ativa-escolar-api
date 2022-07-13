<?php
/**
 * busca-ativa-escolar-api
 * SupportTicket.php
 *
 * Copyright (c) LQDI Digital
 * www.lqdi.net - 2017
 *
 * @author Aryel Tupinambá <aryel.tupinamba@lqdi.net>
 *
 * Created at: 14/07/2017, 16:16
 */

namespace BuscaAtivaEscolar;


use BuscaAtivaEscolar\Traits\Data\IndexedByUUID;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SupportTicket extends Model {

	use SoftDeletes;
	use IndexedByUUID;

	protected $table = "support_tickets";
	protected $fillable = [
		'user_id',
		'tenant_id',
		'name',
		'city_name',
		'email',
		'phone',
		'user_agent',
		'message',
	];

	public function user() {
		return $this->hasOne(User::class, 'id', 'user_id');
	}

	public function tenant() {
		return $this->hasOne(Tenant::class, 'id', 'tenant_id');
	}

	public function getName() {
		if($this->user) return $this->user->name;
		return $this->name;
	}

	public function getCityName() {
		if($this->tenant && $this->tenant) return $this->tenant->name;
		return $this->city_name;
	}

	public function getEmail() {
		if($this->user) return $this->user->email;
		return $this->email;
	}

	public function getPhone() {
		if($this->user) return $this->user->work_phone;
		return $this->phone;
	}

}