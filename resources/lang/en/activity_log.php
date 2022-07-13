<?php
return [

	'child' => [
		'alert_spawned' => "registrou um novo alerta para :CHILD_NAME",
		'alert_accepted' => "ACEITOU o alerta de :CHILD_NAME",
		'alert_rejected' => "REJEITOU o alerta de :CHILD_NAME",
		'step_updated' => "atualizou informações na ficha de :CHILD_NAME",
		'step_assigned' => "atribuiu :ASSIGNED_USER_NAME à etapa :Step_name da ficha de :CHILD_NAME",
		'step_started' => "iniciou a etapa :Step_name para :CHILD_NAME",
		'step_completed' => "concluiu a etapa :Step_name de :CHILD_NAME",
		'added_comment' => "fez uma anotação na ficha de :TARGET_NAME",
		'added_attachment' => "anexou um arquivo na ficha de :TARGET_NAME",
		'status_cancelled' => "cancelou o caso de :CHILD_NAME, com o motivo :REASON_DESCRIPTION",
		'status_interrupted' => "reiniciou o caso de :CHILD_NAME, devido a nova evasão escolar",
		'status_completed' => "encerrou o caso de :CHILD_NAME, com sucesso na (re)matrícula escolar",
	],

	'user' => [
		'created' => 'cadastrou um novo usuário, :USER_NAME, :USER_TYPE_NAME',
		'updated' => 'atualizou informações do usuário :USER_NAME',
		'deleted' => 'excluiu o cadastro do usuário :USER_NAME',
		'suspended' => 'suspendeu o usuário :USER_NAME',
		'reactivated' => 'reativou o usuário :USER_NAME',
	],

	'settings' => [
		'updated_flow' => 'atualizou as configurações de encaminhamento do município',
		'updated_groups' => 'atualizou a lista de grupos do município',
		'updated_deadlines' => 'atualizou as configurações de prazos do município',
	],

	'tenant' => [
		'signed_up' => 'cadastrou seu município, :CITY_NAME, na plataforma Busca Ativa Escolar',
		'approved' => 'aprovou o cadastro do município :CITY_NAME na plataforma Busca Ativa Escolar',
		'rejected' => 'rejeitou o cadastro do município :CITY_NAME na plataforma Busca Ativa Escolar',
		'activated' => 'concluiu as configurações de município e ativou :CITY_NAME para uso na plataforma Busca Ativa Escolar',
	],

];