<?php

use BuscaAtivaEscolar\User;

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
        'update.yourself',
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
        'cities.selo_reports',
        'users.reports',
        'update.yourself',
    ],

    BuscaAtivaEscolar\User::TYPE_COMITE_NACIONAL => [
        'reports.view',
        'reports.ufs',
        'reports.tenants',
        'reports.signups',
        'cases.map',
        'tenants.view',
        'ufs.view',
        'update.yourself',
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
        'update.yourself',
        'update.school',
    ],

    BuscaAtivaEscolar\User::TYPE_GESTOR_ESTADUAL => [
        'reports.view',
        'cases.map',
        'cases.export.uf',
        'users.view',
        'users.manage',
        'users.export',
        'tenants.view',
        'tenants.activity',
        'tenants.contact_info',
        'tenants.export',
        'preferences',
        'update.yourself',
    ],

    BuscaAtivaEscolar\User::TYPE_COORDENADOR_ESTADUAL => [
        'cases.manage',
        'cases.assign',
        'reports.view',
        'cases.map',
        'cases.export.uf',
        'users.view',
        'users.manage',
        'users.export',
        'tenants.view',
        'tenants.activity',
        'tenants.contact_info',
        'tenants.export',
        'preferences',
        'cases.view',
        'cases.step.alerta',
        'cases.step.pesquisa',
        'cases.step.analise_tecnica',
        'cases.step.gestao_do_caso',
        'cases.step.rematricula',
        'cases.step.1a_observacao',
        'cases.step.2a_observacao',
        'cases.step.3a_observacao',
        'cases.step.4a_observacao',
        'school.list',
        'update.yourself',
        'update.school'
    ],

    BuscaAtivaEscolar\User::TYPE_COMITE_ESTADUAL => [
        'reports.view',
        'cases.map',
        'tenants.view',
        'tenants.contact_info',
        'preferences',
        'update.yourself',
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
        'cases.request-transfer',
        'cases.export.all',
        'requests.view',
        'requests.reject',
        'cases.transfer',
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
        'update.school',
        'tenant.complete_setup',
        'preferences',
        'notifications',
        'groups.manage',
        'school.list',
        'update.yourself',
    ],

    BuscaAtivaEscolar\User::TYPE_SUPERVISOR_INSTITUCIONAL => [
        'reports.view',
        'users.view',
        'users.manage',
        'cases.view',
        'cases.manage',
        'cases.cancel',
        'cases.assign',
        'cases.request-reopen',
        'cases.map',
        'cases.step.alerta',
        'cases.export.all',
        'requests.view',
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
        'update.school',
        'school.list',
        'update.yourself',
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
        'cases.export.uf',
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
        'school.list',
        'update.yourself',
        'update.school',
    ],

    BuscaAtivaEscolar\User::TYPE_TECNICO_VERIFICADOR => [
        'reports.view',
        'cases.view',
        'cases.manage',
        'cases.map',
        'cases.export.all',
        'cases.step.alerta',
        'cases.step.pesquisa',
        'cases.step.analise_tecnica',
        'alerts.spawn',
        'preferences',
        'notifications',
        'update.yourself',
    ],

    BuscaAtivaEscolar\User::TYPE_AGENTE_COMUNITARIO => [
        'alerts.spawn',
        'update.yourself',
    ],


    BuscaAtivaEscolar\User::TYPE_VISITANTE_NACIONAL_UM => [
        'reports.view',
        'reports.ufs',
        'users.view',
        'cases.view',

        'cases.step.alerta',
        'cases.step.pesquisa',
        'cases.step.analise_tecnica',
        'cases.step.gestao_do_caso',
        'cases.step.rematricula',
        'cases.step.1a_observacao',
        'cases.step.2a_observacao',
        'cases.step.3a_observacao',
        'cases.step.4a_observacao',

        'update.yourself',
    ],

    BuscaAtivaEscolar\User::TYPE_VISITANTE_NACIONAL_DOIS => [
        'reports.view',
        'reports.ufs',
        'users.view',
        //        'cases.view',
        //
        //        'cases.step.alerta',
        //        'cases.step.pesquisa',
        //        'cases.step.analise_tecnica',
        //        'cases.step.gestao_do_caso',
        //        'cases.step.rematricula',
        //        'cases.step.1a_observacao',
        //        'cases.step.2a_observacao',
        //        'cases.step.3a_observacao',
        //        'cases.step.4a_observacao',

        'update.yourself',
    ],

    BuscaAtivaEscolar\User::TYPE_VISITANTE_NACIONAL_TRES => [
        'reports.view',
        'reports.ufs',
        //        'users.view',
        //        'cases.view',
        //
        //        'cases.step.alerta',
        //        'cases.step.pesquisa',
        //        'cases.step.analise_tecnica',
        //        'cases.step.gestao_do_caso',
        //        'cases.step.rematricula',
        //        'cases.step.1a_observacao',
        //        'cases.step.2a_observacao',
        //        'cases.step.3a_observacao',
        //        'cases.step.4a_observacao',

        'update.yourself',
    ],

    BuscaAtivaEscolar\User::TYPE_VISITANTE_NACIONAL_QUATRO => [
        'reports.view',
        //        'reports.ufs',
        //        'users.view',
        'cases.view',

        'cases.step.alerta',
        'cases.step.pesquisa',
        'cases.step.analise_tecnica',
        'cases.step.gestao_do_caso',
        'cases.step.rematricula',
        'cases.step.1a_observacao',
        'cases.step.2a_observacao',
        'cases.step.3a_observacao',
        'cases.step.4a_observacao',

        'update.yourself',
    ],


    BuscaAtivaEscolar\User::TYPE_VISITANTE_ESTADUAL_UM => [
        'reports.view',
        'reports.ufs',
        'users.view',
        'users.export',
        'tenants.view',
        'tenants.export',
        //        'cases.view',
        //
        //        'cases.step.alerta',
        //        'cases.step.pesquisa',
        //        'cases.step.analise_tecnica',
        //        'cases.step.gestao_do_caso',
        //        'cases.step.rematricula',
        //        'cases.step.1a_observacao',
        //        'cases.step.2a_observacao',
        //        'cases.step.3a_observacao',
        //        'cases.step.4a_observacao',

        'update.yourself',
    ],

    BuscaAtivaEscolar\User::TYPE_VISITANTE_ESTADUAL_DOIS => [
        'reports.view',
        'reports.ufs',
        'users.view',
        'users.export',
        'tenants.view',
        'tenants.export',
        //        'cases.view',
        //
        //        'cases.step.alerta',
        //        'cases.step.pesquisa',
        //        'cases.step.analise_tecnica',
        //        'cases.step.gestao_do_caso',
        //        'cases.step.rematricula',
        //        'cases.step.1a_observacao',
        //        'cases.step.2a_observacao',
        //        'cases.step.3a_observacao',
        //        'cases.step.4a_observacao',

        'update.yourself',
    ],

    BuscaAtivaEscolar\User::TYPE_VISITANTE_ESTADUAL_TRES => [
        'reports.view',
        'reports.ufs',
        'users.view',
        'users.export',
        'tenants.view',
        'tenants.export',
        //        'cases.view',
        //
        //        'cases.step.alerta',
        //        'cases.step.pesquisa',
        //        'cases.step.analise_tecnica',
        //        'cases.step.gestao_do_caso',
        //        'cases.step.rematricula',
        //        'cases.step.1a_observacao',
        //        'cases.step.2a_observacao',
        //        'cases.step.3a_observacao',
        //        'cases.step.4a_observacao',

        'update.yourself',
    ],

    BuscaAtivaEscolar\User::TYPE_VISITANTE_ESTADUAL_QUATRO => [
        'reports.view',
        //        'reports.ufs',
        'users.view',
        'users.export',
        'tenants.view',
        'tenants.export',
        //        'cases.view',
        //
        //        'cases.step.alerta',
        //        'cases.step.pesquisa',
        //        'cases.step.analise_tecnica',
        //        'cases.step.gestao_do_caso',
        //        'cases.step.rematricula',
        //        'cases.step.1a_observacao',
        //        'cases.step.2a_observacao',
        //        'cases.step.3a_observacao',
        //        'cases.step.4a_observacao',

        'update.yourself',
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

            BuscaAtivaEscolar\User::TYPE_VISITANTE,

            BuscaAtivaEscolar\User::TYPE_VISITANTE_NACIONAL_UM,
            BuscaAtivaEscolar\User::TYPE_VISITANTE_NACIONAL_DOIS,
            BuscaAtivaEscolar\User::TYPE_VISITANTE_NACIONAL_TRES,
            BuscaAtivaEscolar\User::TYPE_VISITANTE_NACIONAL_QUATRO,

            BuscaAtivaEscolar\User::TYPE_VISITANTE_ESTADUAL_UM,
            BuscaAtivaEscolar\User::TYPE_VISITANTE_ESTADUAL_DOIS,
            BuscaAtivaEscolar\User::TYPE_VISITANTE_ESTADUAL_TRES,
            BuscaAtivaEscolar\User::TYPE_VISITANTE_ESTADUAL_QUATRO

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

            BuscaAtivaEscolar\User::TYPE_VISITANTE,

            BuscaAtivaEscolar\User::TYPE_VISITANTE_NACIONAL_UM,
            BuscaAtivaEscolar\User::TYPE_VISITANTE_NACIONAL_DOIS,
            BuscaAtivaEscolar\User::TYPE_VISITANTE_NACIONAL_TRES,
            BuscaAtivaEscolar\User::TYPE_VISITANTE_NACIONAL_QUATRO,

            BuscaAtivaEscolar\User::TYPE_VISITANTE_ESTADUAL_UM,
            BuscaAtivaEscolar\User::TYPE_VISITANTE_ESTADUAL_DOIS,
            BuscaAtivaEscolar\User::TYPE_VISITANTE_ESTADUAL_TRES,
            BuscaAtivaEscolar\User::TYPE_VISITANTE_ESTADUAL_QUATRO
        ],
        BuscaAtivaEscolar\User::TYPE_GESTOR_ESTADUAL => [
            BuscaAtivaEscolar\User::TYPE_GESTOR_ESTADUAL,
            BuscaAtivaEscolar\User::TYPE_COORDENADOR_ESTADUAL,
            BuscaAtivaEscolar\User::TYPE_SUPERVISOR_ESTADUAL,
            BuscaAtivaEscolar\User::TYPE_COMITE_ESTADUAL,
        ],
        BuscaAtivaEscolar\User::TYPE_COORDENADOR_ESTADUAL => [
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
            BuscaAtivaEscolar\User::TYPE_AGENTE_COMUNITARIO
        ],
        BuscaAtivaEscolar\User::TYPE_GESTOR_POLITICO => [
            BuscaAtivaEscolar\User::TYPE_GESTOR_OPERACIONAL,
            BuscaAtivaEscolar\User::TYPE_GESTOR_POLITICO,
            BuscaAtivaEscolar\User::TYPE_SUPERVISOR_INSTITUCIONAL,
            BuscaAtivaEscolar\User::TYPE_TECNICO_VERIFICADOR,
            BuscaAtivaEscolar\User::TYPE_AGENTE_COMUNITARIO
        ],
        BuscaAtivaEscolar\User::TYPE_SUPERVISOR_INSTITUCIONAL => [
            BuscaAtivaEscolar\User::TYPE_SUPERVISOR_INSTITUCIONAL,
            BuscaAtivaEscolar\User::TYPE_TECNICO_VERIFICADOR,
            BuscaAtivaEscolar\User::TYPE_AGENTE_COMUNITARIO
        ],
        BuscaAtivaEscolar\User::TYPE_TECNICO_VERIFICADOR => [
            BuscaAtivaEscolar\User::TYPE_TECNICO_VERIFICADOR
        ],
        BuscaAtivaEscolar\User::TYPE_AGENTE_COMUNITARIO => [
            BuscaAtivaEscolar\User::TYPE_AGENTE_COMUNITARIO
        ],
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

            BuscaAtivaEscolar\User::TYPE_VISITANTE_NACIONAL,
            BuscaAtivaEscolar\User::TYPE_VISITANTE_ESTADUAL,
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

            BuscaAtivaEscolar\User::TYPE_VISITANTE_NACIONAL,
            BuscaAtivaEscolar\User::TYPE_VISITANTE_ESTADUAL,
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

        BuscaAtivaEscolar\User::TYPE_VISITANTE_NACIONAL_UM => [
            BuscaAtivaEscolar\User::TYPE_GESTOR_ESTADUAL,
            BuscaAtivaEscolar\User::TYPE_COORDENADOR_ESTADUAL,
            BuscaAtivaEscolar\User::TYPE_GESTOR_POLITICO,
            BuscaAtivaEscolar\User::TYPE_GESTOR_OPERACIONAL
        ],

        BuscaAtivaEscolar\User::TYPE_VISITANTE_NACIONAL_DOIS => [
            BuscaAtivaEscolar\User::TYPE_GESTOR_ESTADUAL,
            BuscaAtivaEscolar\User::TYPE_COORDENADOR_ESTADUAL,
            BuscaAtivaEscolar\User::TYPE_GESTOR_POLITICO,
            BuscaAtivaEscolar\User::TYPE_GESTOR_OPERACIONAL
        ],

        BuscaAtivaEscolar\User::TYPE_VISITANTE_NACIONAL_TRES => [
            BuscaAtivaEscolar\User::TYPE_GESTOR_ESTADUAL,
            BuscaAtivaEscolar\User::TYPE_COORDENADOR_ESTADUAL,
            BuscaAtivaEscolar\User::TYPE_GESTOR_POLITICO,
            BuscaAtivaEscolar\User::TYPE_GESTOR_OPERACIONAL
        ],

        BuscaAtivaEscolar\User::TYPE_VISITANTE_NACIONAL_QUATRO => [
            BuscaAtivaEscolar\User::TYPE_GESTOR_ESTADUAL,
            BuscaAtivaEscolar\User::TYPE_COORDENADOR_ESTADUAL,
            BuscaAtivaEscolar\User::TYPE_GESTOR_POLITICO,
            BuscaAtivaEscolar\User::TYPE_GESTOR_OPERACIONAL
        ],


        BuscaAtivaEscolar\User::TYPE_VISITANTE_ESTADUAL_UM => [
            BuscaAtivaEscolar\User::TYPE_GESTOR_ESTADUAL,
            BuscaAtivaEscolar\User::TYPE_COORDENADOR_ESTADUAL,
            BuscaAtivaEscolar\User::TYPE_GESTOR_POLITICO,
            BuscaAtivaEscolar\User::TYPE_GESTOR_OPERACIONAL
        ],

        BuscaAtivaEscolar\User::TYPE_VISITANTE_ESTADUAL_DOIS => [
            BuscaAtivaEscolar\User::TYPE_GESTOR_ESTADUAL,
            BuscaAtivaEscolar\User::TYPE_COORDENADOR_ESTADUAL,
            BuscaAtivaEscolar\User::TYPE_GESTOR_POLITICO,
            BuscaAtivaEscolar\User::TYPE_GESTOR_OPERACIONAL
        ],

        BuscaAtivaEscolar\User::TYPE_VISITANTE_ESTADUAL_TRES => [
            BuscaAtivaEscolar\User::TYPE_GESTOR_ESTADUAL,
            BuscaAtivaEscolar\User::TYPE_COORDENADOR_ESTADUAL,
            BuscaAtivaEscolar\User::TYPE_GESTOR_POLITICO,
            BuscaAtivaEscolar\User::TYPE_GESTOR_OPERACIONAL
        ],

        BuscaAtivaEscolar\User::TYPE_VISITANTE_ESTADUAL_QUATRO => [
            BuscaAtivaEscolar\User::TYPE_GESTOR_ESTADUAL,
            BuscaAtivaEscolar\User::TYPE_COORDENADOR_ESTADUAL,
            BuscaAtivaEscolar\User::TYPE_GESTOR_POLITICO,
            BuscaAtivaEscolar\User::TYPE_GESTOR_OPERACIONAL
        ],


    ]

];
