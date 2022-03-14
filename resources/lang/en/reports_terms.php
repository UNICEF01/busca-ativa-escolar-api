<?php
return [

    'gender' => [
        'male' => 'Masculino',
        'female' => 'Feminino',
        'null' => 'Não declarado',
    ],

    //    'status' => [
    //        'out_of_school' => 'Fora da escola',
    //        'in_observation' => 'Em observação',
    //        'in_school' => 'Dentro da escola',
    //        'cancelled' => 'Cancelado',
    //        'completed' => 'Completo',
    //        'in_progress' => 'Em andamento',
    //        'interrupted' => 'Interrompido',
    //        'transferred' => 'Transferido'
    //    ],

    //    'status' => [
    //        'out_of_school' => 'Caso em andamento: de pesquisa até (re)matrícula',
    //        'in_observation' => 'Dentro da escola e em observação: de 1ª a 4ª observação',
    //        'in_school' => 'Casos concluídos: caso finalizado com sucesso após a 4ª observação',
    //        'cancelled' => 'Caso cancelado: em qualquer etapa do processo',
    //        'completed' => 'Completo',
    //        'in_progress' => 'Em andamento',
    //        'interrupted' => 'Casos interrompidos: criança ou adolescente que evadiu durante as etapas de observação e cujo caso não foi reaberto',
    //        'transferred' => 'Transferido'
    //    ],

    'status' => [
        'out_of_school' => 'Em andamento/ Fora da escola',
        'cancelled' => 'Casos cancelados',
        'in_observation' => 'Em andamento/ Dentro da escola',
        'in_school' => 'Casos concluídos',
        'interrupted' => 'Casos interrompidos',

        'completed' => 'Completos',
        'in_progress' => 'Em andamento',
        'transferred' => 'Transferidos'
    ],

    'name_by_slug' => [
        'alerta' => 'Alerta',
        'pesquisa' => 'Pesquisa',
        'analise_tecnica' => 'Análise Técnica',
        'gestao_do_caso' => 'Gestão do Caso',
        'rematricula' => '(Re)matrícula',
        '1a_observacao' => "1ª Observação",
        '2a_observacao' => "2ª Observação",
        '3a_observacao' => "3ª Observação",
        '4a_observacao' => "4ª Observação",
    ],

    'age_ranges' => [
        '0.0-4.0' => 'de 0 a 3 anos',
        '4.0-6.0' => 'de 4 a 5 anos',
        '6.0-11.0' => 'de 6 a 10 anos',
        '11.0-15.0' => 'de 11 a 14 anos',
        '15.0-18.0' => 'de 15 a 17 anos',
        '18.0-5001.0' => 'mais de 18 anos',
        '0' => 'Não informado',
    ],

    'parents_income' => [
        'up_to_quarter' => 'Até ¼',
        'between_quarter_and_half' => 'Mais de ¼ a ½',
        'between_one_and_two' => 'Mais de 1 a 2',
        'over_2' => 'Mais de 2',
        'null' => 'Não informado',
    ],

    'alert_status' => [
        "pending" => 'Pendente',
        "accepted" => 'Procedente',
        "rejected" => 'Improcendente',
    ],

    'school_last_id' =>[
        'null' => 'Não informada'
    ],

    'deadline_status' => [
        'normal' => 'OK',
        'late' => 'Em atraso',
    ],

    'gender_icon' => [
        'male' => 'fa-mars',
        'female' => 'fa-venus',
        'undefined' => 'fa-transgender',
        'null' => 'fa-transgender-alt',
    ],

    'races' => [
        'indigena' => "Indígena",
        'branca' => "Branca",
        'preta' => "Preta",
        'amarela' => "Amarela",
        'null' => "Não informado",
        'no_info' => "Não declarado",
        'negra_pretos_e_pardos' => "Negra (pretos e pardos)",
        'parda' => "Parda"
    ],

    'risk_level' => [
        'high' => 'Alto',
        'medium' => 'Médio',
        'low' => 'Baixo',
    ],

    'place_kind' => [
        'urban' => 'Urbano',
        'rural' => 'Rural',
        'null' => "Não informado",
    ],

    'work_activity' => [
        'servico_domestico' => 'Serviço doméstico',
        'negocio_familiar' => 'Negócio familiar',
        'other' => 'Outra atividade',
        'null' => 'Não informado'
    ],

    'guardian_schooling' => [
        'nenhuma' => 'Nenhuma',
        'ef_incompleto' => 'Ensino fundamental incompleto',
        'ef_completo' => 'Ensino fundamental completo',
        'em_incompleto' => 'Ensino médio incompleto',
        'em_completo' => 'Ensino médio completo',
        'superior_incompleto' => 'Ensino superior incompleto',
        'superior_completo' => 'Ensino superior completo',
        'posgraduacao' => 'Pós graduação',
        'null' => 'Não informado'
    ],

    'country_region' => [
        'se' => 'Sudeste',
        'ne' => 'Nordeste',
        'no' => 'Norte',
        'co' => 'Centro-Oeste',
        'su' => 'Sul',
    ],

    'school_last_grade' => [
        'creche_1ano' => 'Creche (1 ano)',
        'creche_2ano' => 'Creche (2 anos)',
        'creche_3ano' => 'Creche (3 anos)',
        'creche_4ano' => 'Creche (4 anos)',
        'preescola_4anos' => 'Pré-escola (4 anos)',
        'preescola_5anos' => 'Pré-escola (5 anos)',
        'ef_1ano' => 'Ensino Fundamental - Anos iniciais (1º ano)',
        'ef_2ano' => 'Ensino Fundamental - Anos iniciais (2º ano)',
        'ef_3ano' => 'Ensino Fundamental - Anos iniciais (3º ano)',
        'ef_4ano' => 'Ensino Fundamental - Anos iniciais (4º ano)',
        'ef_5ano' => 'Ensino Fundamental - Anos iniciais (5º ano)',
        'ef_6ano' => 'Ensino Fundamental - Anos iniciais (6º ano)',
        'ef_7ano' => 'Ensino Fundamental - Anos iniciais (7º ano)',
        'ef_8ano' => 'Ensino Fundamental - Anos iniciais (8º ano)',
        'ef_9ano' => 'Ensino Fundamental - Anos iniciais (9º ano)',
        'em_1ano' => 'Ensino Médio - 1º ano',
        'em_2ano' => 'Ensino Médio - 2º ano',
        'em_3ano' => 'Ensino Médio - 3º ano',
        'eja_finais' => 'EJA (ensino fundamental anos finais)',
        'eja_iniciais' => 'EJA (ensino fundamental anos iniciais)',
        'eja_alfabetizado' => 'EJA (Alfabetizado)',
        'null' => 'Não informado'
    ],

    'case_causes' =>
    [
        '10' => 'Adolescente em conflito com a lei',
        '21' => 'Criança ou adolescente com deficiência física',
        '22' => 'Criança ou adolescente com deficiência intelectual',
        '23' => 'Criança ou adolescente com deficiência mental',
        '24' => 'Criança ou adolescente com deficiência sensorial',
        '25' => 'Criança ou adolescente com deficiência(s) que impeça(m) ou dificulte(m) a frequência à escola',
        '30' => 'Criança ou adolescente com doenças (que impedem e/ou dificultem a frequência à escola',
        '40' => 'Criança ou adolescente em abrigo',
        '50' => 'Criança ou adolescente em situação de rua',
        '60' => 'Criança ou adolescente que sofrem ou sofreram abuso / violência sexual',
        '61' => 'Crianças ou adolescentes migrantes estrangeiro',
        '70' => 'Evasão porque sente a escola desinteressante',
        '71' => 'Evasão porque sente a escola desinteressante (Desinteresse pela escola)',
        '72' => 'Evasão porque sente a escola desinteressante (Desinteresse pelos estudos)',
        '80' => 'Falta de documentação da criança ou adolescente',
        '91' => 'Falta de infraestrutura escolar (Escola)',
        '92' => 'Falta de infraestrutura escolar (Vagas)',
        '100' => 'Falta de transporte escolar',
        '101' => 'Falta de transporte escolar (Transporte escolar público)',
        '102' => 'Falta de transporte escolar (Transporte público - ônibus, metrô, trem, balsa, barco etc.)',
        '103' => 'Falta de transporte escolar (Transporte particular - veículo próprio)',
        '110' => 'Gravidez na adolescência',
        '120' => 'Preconceito ou discriminação racial',
        '130' => 'Trabalho infantil',
        '140' => 'Uso, abuso ou dependência de substâncias psicoativas',
        '150' => 'Violência familiar',
        '161' => 'Violência na escola (Discriminação de gênero)',
        '162' => 'Violência na escola (Discriminação racial)',
        '163' => 'Violência na escola (Discriminação religiosa)',
        '164' => 'Violência na escola (discriminação por orientação sexual)',
        '165' => 'Violência na escola (bullying)',
        '166' => 'Violência na escola (conflitos com outros estudantes)',
        '167' => 'Violência na escola (conflitos da criança e/ou adolescente com funcionários, docentes ou gestores da escola)',
        '170' => 'Mudança de domicílio, viagem ou deslocamentos frequentes',
        '180' => 'Violência no território',
        '500' => 'Evasão reportada pelo Educacenso/INEP',
        '600' => 'Evasão e/ou infrequência reportada pela escola ou município',
        'null' => 'Casos pendentes de informação'
    ],

    'place_uf' => [
        'ac' => 'Acre',
        'al' => 'Alagoas',
        'am' => 'Amazonas',
        'ap' => 'Amapá',
        'ba' => 'Bahia',
        'ce' => 'Ceará',
        'df' => 'Distrito Federal',
        'es' => 'Espírito Santo',
        'go' => 'Goiás',
        'ma' => 'Maranhão',
        'mg' => 'Minas Gerais',
        'ms' => 'Mato Grosso do Sul',
        'mt' => 'Mato Grosso',
        'pa' => 'Pará',
        'pb' => 'Paraíba',
        'pe' => 'Pernambuco',
        'pi' => 'Piauí',
        'pr' => 'Paraná',
        'rj' => 'Rio de Janeiro',
        'rn' => 'Rio Grande do Norte',
        'ro' => 'Rondonia',
        'rr' => 'Roraima',
        'rs' => 'Rio Grande do Sul',
        'sc' => 'Santa Catarina',
        'se' => 'Sergipe',
        'sp' => 'São Paulo',
        'to' => 'Tocantins',
        'null' => 'Não Informado'
    ],

    'child_status_by_tenant' => [
        'out_of_school' => 'Em andamento/ Fora da escola',
        'in_observation' => 'Em andamento/ Dentro da escola',
        'in_school' => 'Casos concluídos',
        'cancelled' => 'Casos cancelados',
        'interrupted' => 'Casos interrompidos',
        'transferred'  => 'Transferidos'
    ],

    'alert_status_report_by_tenant' => [
        "pending" => 'Pendente',
        "accepted" => 'Aceito',
        "rejected" => 'Rejeitado',
    ],

];
