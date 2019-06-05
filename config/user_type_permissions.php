<?php
return [

	BuscaAtivaEscolar\User::TYPE_SUPERUSER => [
		'reports.view',
		'reports.ufs',
		'reports.tenants',
		'reports.signups',
		'users.view',
		'users.manage',
		'users.export',
		//'cases.view',
		//'cases.map',
		'tenants.manage',
		'tenants.view',
		'tenants.activity',
		'tenants.contact_info',
		'tenants.export',
		'tenants.export_signups',
		'ufs.view',
		'ufs.manage',
		'ufs.contact_info',
		'developer_tools',
		'maintenance',
	],

	BuscaAtivaEscolar\User::TYPE_GESTOR_NACIONAL => [
		'reports.view',
		'reports.ufs',
		'reports.tenants',
		'reports.signups',
		'cases.map',
		'users.view',
		'users.manage',
		'users.export',
		'tenants.manage',
		'tenants.view',
		'tenants.activity',
		'tenants.contact_info',
		'tenants.export',
		'tenants.export_signups',
		'ufs.view',
		'ufs.manage',
		'ufs.contact_info',
	],

	BuscaAtivaEscolar\User::TYPE_COMITE_NACIONAL => [
		'reports.view',
		'reports.ufs',
		'reports.tenants',
		'reports.signups',
		'cases.map',
		'tenants.view',
		'ufs.view',
	],

	BuscaAtivaEscolar\User::TYPE_GESTOR_POLITICO => [
		'reports.view',
		'cases.map',
		'users.view',
		'users.manage',
		'users.export',
		'alerts.spawn',
		'settings.manage',
		'settings.educacenso',
		'preferences',
		'notifications',
        'groups.manage',
	],

	BuscaAtivaEscolar\User::TYPE_GESTOR_ESTADUAL => [
		'reports.view',
		'cases.map',
		'users.view',
		'users.manage',
		'users.export',
		'tenants.view',
		'tenants.activity',
		'tenants.contact_info',
		'tenants.export',
        'groups.manage',
        'preferences',
    ],

	BuscaAtivaEscolar\User::TYPE_COORDENADOR_ESTADUAL => [
		'reports.view',
		'cases.map',
		'users.view',
		'users.manage',
		'users.export',
		'tenants.view',
		'tenants.activity',
		'tenants.contact_info',
		'tenants.export',
		'groups.manage',
        'preferences',
    ],

	BuscaAtivaEscolar\User::TYPE_COMITE_ESTADUAL => [
		'reports.view',
		'cases.map',
		'tenants.view',
		'tenants.contact_info',
        'preferences',
    ],

	BuscaAtivaEscolar\User::TYPE_GESTOR_OPERACIONAL => [
		'reports.view',
		'users.view',
		'users.manage',
		'users.export',
		'cases.view',
		'cases.manage',
		'cases.cancel',
		'cases.assign',
		'cases.reopen',
		'cases.map',
		'cases.step.alerta',
		'cases.step.pesquisa',
		'cases.step.analise_tecnica',
		'cases.step.gestao_do_caso',
		'cases.step.rematricula',
		'cases.step.1a_observacao',
		'cases.step.2a_observacao',
		'cases.step.3a_observacao',
		'cases.step.4a_observacao',
		'alerts.pending',
		'alerts.spawn',
		'settings.manage',
		'settings.educacenso',
		'tenant.complete_setup',
		'preferences',
		'notifications',
        'groups.manage',
	],

	BuscaAtivaEscolar\User::TYPE_SUPERVISOR_INSTITUCIONAL => [
		'reports.view',
		'users.view',
		'users.manage',
		'cases.view',
		'cases.manage',
		'cases.cancel',
		'cases.assign',
		'cases.reopen',
		'cases.map',
		'cases.step.alerta',
		'cases.step.pesquisa',
		'cases.step.analise_tecnica',
		'cases.step.gestao_do_caso',
		'cases.step.rematricula',
		'cases.step.1a_observacao',
		'cases.step.2a_observacao',
		'cases.step.3a_observacao',
		'cases.step.4a_observacao',
		'alerts.pending',
		'alerts.spawn',
		'preferences',
		'notifications',
        'settings.educacenso',
	],

	BuscaAtivaEscolar\User::TYPE_SUPERVISOR_ESTADUAL => [
		'reports.view',
		'users.view',
		'users.manage',
		'users.export',
		'cases.view',
		'cases.manage',
		'cases.cancel',
		'cases.assign',
		'cases.reopen',
		'cases.map',
		'cases.step.alerta',
		'cases.step.pesquisa',
		'cases.step.analise_tecnica',
		'cases.step.gestao_do_caso',
		'cases.step.rematricula',
		'cases.step.1a_observacao',
		'cases.step.2a_observacao',
		'cases.step.3a_observacao',
		'cases.step.4a_observacao',
		'tenants.view',
		'tenants.activity',
		'tenants.contact_info',
		//'alerts.pending',
		//'alerts.spawn',
		'preferences',
		'notifications',
	],

	BuscaAtivaEscolar\User::TYPE_TECNICO_VERIFICADOR => [
		'reports.view',
		'cases.view',
		'cases.manage',
		'cases.map',
		'cases.step.alerta',
		'cases.step.pesquisa',
		'cases.step.analise_tecnica',
		'alerts.spawn',
		'preferences',
		'notifications',
	],

	BuscaAtivaEscolar\User::TYPE_AGENTE_COMUNITARIO => [
		'alerts.spawn',
	],

	'can_manage_types' => [ // Which users can set/change/create which user types
		BuscaAtivaEscolar\User::TYPE_SUPERUSER => [
			BuscaAtivaEscolar\User::TYPE_SUPERUSER,
			BuscaAtivaEscolar\User::TYPE_GESTOR_NACIONAL,
			BuscaAtivaEscolar\User::TYPE_GESTOR_OPERACIONAL,
			BuscaAtivaEscolar\User::TYPE_GESTOR_POLITICO,
			BuscaAtivaEscolar\User::TYPE_SUPERVISOR_INSTITUCIONAL,
			BuscaAtivaEscolar\User::TYPE_TECNICO_VERIFICADOR,
			BuscaAtivaEscolar\User::TYPE_AGENTE_COMUNITARIO,
			BuscaAtivaEscolar\User::TYPE_GESTOR_ESTADUAL,
			BuscaAtivaEscolar\User::TYPE_COORDENADOR_ESTADUAL,
			BuscaAtivaEscolar\User::TYPE_SUPERVISOR_ESTADUAL,
			BuscaAtivaEscolar\User::TYPE_COMITE_NACIONAL,
			BuscaAtivaEscolar\User::TYPE_COMITE_ESTADUAL,
		],
		BuscaAtivaEscolar\User::TYPE_GESTOR_NACIONAL => [
			BuscaAtivaEscolar\User::TYPE_GESTOR_NACIONAL,
			BuscaAtivaEscolar\User::TYPE_GESTOR_OPERACIONAL,
			BuscaAtivaEscolar\User::TYPE_GESTOR_POLITICO,
			BuscaAtivaEscolar\User::TYPE_SUPERVISOR_INSTITUCIONAL,
			BuscaAtivaEscolar\User::TYPE_TECNICO_VERIFICADOR,
			BuscaAtivaEscolar\User::TYPE_AGENTE_COMUNITARIO,
			BuscaAtivaEscolar\User::TYPE_GESTOR_ESTADUAL,
			BuscaAtivaEscolar\User::TYPE_COORDENADOR_ESTADUAL,
			BuscaAtivaEscolar\User::TYPE_SUPERVISOR_ESTADUAL,
			BuscaAtivaEscolar\User::TYPE_COMITE_NACIONAL,
			BuscaAtivaEscolar\User::TYPE_COMITE_ESTADUAL,
		],
		BuscaAtivaEscolar\User::TYPE_GESTOR_ESTADUAL => [
			BuscaAtivaEscolar\User::TYPE_GESTOR_ESTADUAL,
			BuscaAtivaEscolar\User::TYPE_COORDENADOR_ESTADUAL,
			BuscaAtivaEscolar\User::TYPE_SUPERVISOR_ESTADUAL,
			BuscaAtivaEscolar\User::TYPE_COMITE_ESTADUAL,
		],
		BuscaAtivaEscolar\User::TYPE_COORDENADOR_ESTADUAL=> [
			BuscaAtivaEscolar\User::TYPE_COORDENADOR_ESTADUAL,
			BuscaAtivaEscolar\User::TYPE_SUPERVISOR_ESTADUAL,
			BuscaAtivaEscolar\User::TYPE_COMITE_ESTADUAL,
		],
		BuscaAtivaEscolar\User::TYPE_SUPERVISOR_ESTADUAL => [
			BuscaAtivaEscolar\User::TYPE_SUPERVISOR_ESTADUAL,
		],
		BuscaAtivaEscolar\User::TYPE_GESTOR_OPERACIONAL => [
			BuscaAtivaEscolar\User::TYPE_GESTOR_OPERACIONAL,
			BuscaAtivaEscolar\User::TYPE_GESTOR_POLITICO,
			BuscaAtivaEscolar\User::TYPE_SUPERVISOR_INSTITUCIONAL,
			BuscaAtivaEscolar\User::TYPE_TECNICO_VERIFICADOR,
			BuscaAtivaEscolar\User::TYPE_AGENTE_COMUNITARIO,
		],
		BuscaAtivaEscolar\User::TYPE_GESTOR_POLITICO => [
			BuscaAtivaEscolar\User::TYPE_GESTOR_OPERACIONAL,
			BuscaAtivaEscolar\User::TYPE_GESTOR_POLITICO,
			BuscaAtivaEscolar\User::TYPE_SUPERVISOR_INSTITUCIONAL,
			BuscaAtivaEscolar\User::TYPE_TECNICO_VERIFICADOR,
			BuscaAtivaEscolar\User::TYPE_AGENTE_COMUNITARIO,
		],
		BuscaAtivaEscolar\User::TYPE_SUPERVISOR_INSTITUCIONAL => [
			BuscaAtivaEscolar\User::TYPE_TECNICO_VERIFICADOR,
			BuscaAtivaEscolar\User::TYPE_AGENTE_COMUNITARIO,
		],
		BuscaAtivaEscolar\User::TYPE_TECNICO_VERIFICADOR => [],
		BuscaAtivaEscolar\User::TYPE_AGENTE_COMUNITARIO => [],
	],

	'can_filter_types' => [ // Which users can set/change/create which user types
		BuscaAtivaEscolar\User::TYPE_SUPERUSER => [
			BuscaAtivaEscolar\User::TYPE_SUPERUSER,
			BuscaAtivaEscolar\User::TYPE_GESTOR_NACIONAL,
			BuscaAtivaEscolar\User::TYPE_GESTOR_OPERACIONAL,
			BuscaAtivaEscolar\User::TYPE_GESTOR_POLITICO,
			BuscaAtivaEscolar\User::TYPE_SUPERVISOR_INSTITUCIONAL,
			BuscaAtivaEscolar\User::TYPE_TECNICO_VERIFICADOR,
			BuscaAtivaEscolar\User::TYPE_AGENTE_COMUNITARIO,
			BuscaAtivaEscolar\User::TYPE_GESTOR_ESTADUAL,
			BuscaAtivaEscolar\User::TYPE_COORDENADOR_ESTADUAL,
			BuscaAtivaEscolar\User::TYPE_SUPERVISOR_ESTADUAL,
			BuscaAtivaEscolar\User::TYPE_COMITE_NACIONAL,
			BuscaAtivaEscolar\User::TYPE_COMITE_ESTADUAL,
		],
		BuscaAtivaEscolar\User::TYPE_GESTOR_NACIONAL => [
			BuscaAtivaEscolar\User::TYPE_SUPERUSER,
			BuscaAtivaEscolar\User::TYPE_GESTOR_NACIONAL,
			BuscaAtivaEscolar\User::TYPE_GESTOR_OPERACIONAL,
			BuscaAtivaEscolar\User::TYPE_GESTOR_POLITICO,
			BuscaAtivaEscolar\User::TYPE_SUPERVISOR_INSTITUCIONAL,
			BuscaAtivaEscolar\User::TYPE_TECNICO_VERIFICADOR,
			BuscaAtivaEscolar\User::TYPE_AGENTE_COMUNITARIO,
			BuscaAtivaEscolar\User::TYPE_GESTOR_ESTADUAL,
			BuscaAtivaEscolar\User::TYPE_COORDENADOR_ESTADUAL,
			BuscaAtivaEscolar\User::TYPE_SUPERVISOR_ESTADUAL,
			BuscaAtivaEscolar\User::TYPE_COMITE_NACIONAL,
			BuscaAtivaEscolar\User::TYPE_COMITE_ESTADUAL,
		],
		BuscaAtivaEscolar\User::TYPE_GESTOR_ESTADUAL => [
			BuscaAtivaEscolar\User::TYPE_GESTOR_ESTADUAL,
			BuscaAtivaEscolar\User::TYPE_SUPERVISOR_ESTADUAL,
			BuscaAtivaEscolar\User::TYPE_COMITE_ESTADUAL,
		],
		BuscaAtivaEscolar\User::TYPE_COORDENADOR_ESTADUAL => [
			BuscaAtivaEscolar\User::TYPE_GESTOR_ESTADUAL,
			BuscaAtivaEscolar\User::TYPE_SUPERVISOR_ESTADUAL,
			BuscaAtivaEscolar\User::TYPE_COMITE_ESTADUAL,
		],
		BuscaAtivaEscolar\User::TYPE_SUPERVISOR_ESTADUAL => [
			BuscaAtivaEscolar\User::TYPE_GESTOR_ESTADUAL,
			BuscaAtivaEscolar\User::TYPE_SUPERVISOR_ESTADUAL,
		],
		BuscaAtivaEscolar\User::TYPE_GESTOR_OPERACIONAL => [
			BuscaAtivaEscolar\User::TYPE_GESTOR_OPERACIONAL,
			BuscaAtivaEscolar\User::TYPE_GESTOR_POLITICO,
			BuscaAtivaEscolar\User::TYPE_SUPERVISOR_INSTITUCIONAL,
			BuscaAtivaEscolar\User::TYPE_TECNICO_VERIFICADOR,
			BuscaAtivaEscolar\User::TYPE_AGENTE_COMUNITARIO,
		],
		BuscaAtivaEscolar\User::TYPE_GESTOR_POLITICO => [
			BuscaAtivaEscolar\User::TYPE_GESTOR_OPERACIONAL,
			BuscaAtivaEscolar\User::TYPE_GESTOR_POLITICO,
			BuscaAtivaEscolar\User::TYPE_SUPERVISOR_INSTITUCIONAL,
			BuscaAtivaEscolar\User::TYPE_TECNICO_VERIFICADOR,
			BuscaAtivaEscolar\User::TYPE_AGENTE_COMUNITARIO,
		],
		BuscaAtivaEscolar\User::TYPE_SUPERVISOR_INSTITUCIONAL => [
			BuscaAtivaEscolar\User::TYPE_GESTOR_OPERACIONAL,
			BuscaAtivaEscolar\User::TYPE_GESTOR_POLITICO,
			BuscaAtivaEscolar\User::TYPE_SUPERVISOR_INSTITUCIONAL,
			BuscaAtivaEscolar\User::TYPE_TECNICO_VERIFICADOR,
			BuscaAtivaEscolar\User::TYPE_AGENTE_COMUNITARIO,
		],
		BuscaAtivaEscolar\User::TYPE_TECNICO_VERIFICADOR => [
			BuscaAtivaEscolar\User::TYPE_GESTOR_OPERACIONAL,
			BuscaAtivaEscolar\User::TYPE_GESTOR_POLITICO,
			BuscaAtivaEscolar\User::TYPE_SUPERVISOR_INSTITUCIONAL,
			BuscaAtivaEscolar\User::TYPE_TECNICO_VERIFICADOR,
			BuscaAtivaEscolar\User::TYPE_AGENTE_COMUNITARIO,
		],
		BuscaAtivaEscolar\User::TYPE_AGENTE_COMUNITARIO => [],
	]

];