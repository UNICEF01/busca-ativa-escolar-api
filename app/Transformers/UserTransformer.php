<?php
/**
 * busca-ativa-escolar-api
 * UserTransformer.php
 *
 * Copyright (c) LQDI Digital
 * www.lqdi.net - 2017
 *
 * @author Aryel Tupinambá <aryel.tupinamba@lqdi.net>
 *
 * Created at: 08/01/2017, 24:12
 */

namespace BuscaAtivaEscolar\Transformers;


use BuscaAtivaEscolar\User;
use League\Fractal\TransformerAbstract;

class UserTransformer extends TransformerAbstract {

	protected $availableIncludes = [
		'tenant',
		'group',
	];

	protected $defaultIncludes = [
		'tenant',
		'group'
	];

	public function __construct($mode = 'short') {
		$this->mode = $mode;
	}

	public function transform(User $user) {

		$canSeeContactInfo = auth()->user()->canSeeContactInfoFor($user);

		return [
			'id' => $user->id,
			'type' => $user->type,

			'name' => $user->name,
			'email' => $canSeeContactInfo ? $user->email : null,

			'contact_phone' => $canSeeContactInfo ? $user->getContactPhone() : null,

			'tenant_id' => $user->tenant_id,
			'group_id' => $user->group_id,
            'group_name' => $user->group ? $user->group->name : null,
			'city_id' => $user->city_id,
			'uf' => $user->uf,

			'institution' => $user->institution,
			'position' => $user->position,

			'created_at' => $user->created_at,
			'deleted_at' => $user->deleted_at,
            'lgpd' => $user->lgpd

			] + (($this->mode == 'long') ? [

				'permissions' => $user->getPermissions(),
				'can_manage' => $user->getWhoCanManage(),
				'can_filter' => $user->getWhoCanFilter(),

				'cpf' => $canSeeContactInfo ? $user->cpf : null,
				'dob' => $canSeeContactInfo ? $user->dob : null,
				'work_phone' => $canSeeContactInfo ? $user->work_phone : null,
				'work_mobile' => $canSeeContactInfo ? $user->work_mobile : null,
				'personal_mobile' => $canSeeContactInfo ? $user->personal_mobile : null,
				'skype_username' => $canSeeContactInfo ? $user->skype_username : null,
				'work_address' => $user->work_address,
				'work_cep' => $user->work_cep,
				'work_neighborhood' => $user->work_neighborhood,
				'work_city' => ($user->work_city_id) ? [
					'id' => $user->work_city_id,
					'name' => $user->work_city_name,
					'uf' => $user->work_uf,
				] : null,
				'work_uf' => $user->work_uf,
				'lgpd' => $user->lgpd,

			] : []);

	}

	public function includeTenant(User $user) {
		if(!$user->tenant) return null;
		return $this->item($user->tenant, new TenantTransformer(), false);
	}

	public function includeGroup(User $user) {
		if(!$user->group) return null;
		return $this->item($user->group, new GroupTransformer(), false);
	}

}