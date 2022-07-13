<?php
return [
	'risk_level' => [
		'high' => 'Alto',
		'medium' => 'Médio',
		'low' => 'Baixo',
	],

	'status' => [
		 'cancelled' => 'Cancelado',
		 'in_progress' => 'Em andamento',
		 'interrupted' => 'Interrompido',
		 'completed' => 'Concluído',
         'transferred' => 'Transferido',
	],

	'cancel_reason' => [
		"duplicate" => 'Caso duplicado',
		"death" => 'Óbito',
		"not_found" => 'Criança não foi encontrada',
		"wrongful_insertion" => 'Caso inserido por engano',
        "city_transfer" => 'Mudança de município/ estado',
		//"rejected_alert" => 'Alerta não procedente',
	]
];