<?php

return [

    'accepted'             => 'Você deve aceitar o campo \':attribute\'.',
    'active_url'           => 'O campo \':attribute\' não é uma URL válida.',
    'after'                => 'O campo \':attribute\' deve ser uma data após :date.',
    'alpha'                => 'O campo \':attribute\' deve conter apenas letras.',
    'alpha_dash'           => 'O campo \':attribute\' deve conter apenas letras, números e pontuação.',
    'alpha_num'            => 'O campo \':attribute\' deve conter apenas letras e números.',
    'array'                => 'O campo \':attribute\' deve ser uma lista.',
    'before'               => 'O campo \':attribute\' deve ser uma data após :date.',
    'between'              => [
        'numeric' => 'O campo \':attribute\' deve ter entre :min e :max.',
        'file'    => 'O campo \':attribute\' deve ter entre :min e :max kilobytes.',
        'string'  => 'O campo \':attribute\' deve ter entre :min e :max caracteres.',
        'array'   => 'O campo \':attribute\' deve ter entre :min e :max ítens.',
    ],
    'boolean'              => 'O campo \':attribute\' deve ser verdadeiro ou falso.',
    'confirmed'            => 'A confirmação do campo \':attribute\' está incorreta.',
    'date'                 => 'O campo \':attribute\' não é uma data válida.',
    'date_format'          => 'O campo \':attribute\' não é uma data no formato :format.',
    'different'            => 'Os campos \':attribute\' e \':other\' devem ser diferentes.',
    'digits'               => 'O campo \':attribute\' deve ter :digits dígitos.',
    'digits_between'       => 'O campo \':attribute\' deve ter entre :min e :max dígitos.',
    'dimensions'           => 'A imagem \':attribute\' não está de acordo com as especificações.',
    'distinct'             => 'O campo \':attribute\' contém um valor duplicado.',
    'email'                => 'O campo \':attribute\' deve ser um e-mail válido.',
    'exists'               => 'O \':attribute\' selecionado não existe.',
    'file'                 => 'O campo \':attribute\' deve ser um arquivo.',
    'filled'               => 'O campo \':attribute\' é obrigatório.',
    'image'                => 'O campo \':attribute\' deve ser uma imagem.',
    'in'                   => 'O valor do campo \':attribute\' não é válido.',
    'in_array'             => 'O campo \':attribute\' não existe em \':other\'.',
    'integer'              => 'O campo \':attribute\' deve ser um inteiro.',
    'ip'                   => 'O campo \':attribute\' deve ser um endereço de IP válido.',
    'json'                 => 'O campo \':attribute\' deve ser uma string JSON válida.',
    'max'                  => [
        'numeric' => 'O campo \':attribute\' não deve ser maior que :max.',
        'file'    => 'O arquivo \':attribute\' não deve ter mais que :max kilobytes.',
        'string'  => 'O campo \':attribute\' não deve ser maior que :max caracteres.',
        'array'   => 'O campo \':attribute\' não deve ter mais que :max items.',
    ],
    'mimes'                => 'O campo \':attribute\' deve ser um arquivo do tipo: :values.',
    'mimetypes'            => 'O campo \':attribute\' deve ser um arquivo do tipo: :values.',
    'min'                  => [
        'numeric' => 'O campo \':attribute\' deve ter no mínimo :min.',
        'file'    => 'O arquivo \':attribute\' deve ter no mínimo :min kilobytes.',
        'string'  => 'O campo \':attribute\' deve ter no mínimo :min caracteres.',
        'array'   => 'O campo \':attribute\' deve ter no mínimo :min ítens.',
    ],
    'not_in'               => 'O valor do campo \':attribute\' não é válido.',
    'numeric'              => 'O campo \':attribute\' deve ser um número.',
    'present'              => 'O campo \':attribute\' deve estar presente.',
    'regex'                => 'O formato do campo \':attribute\' é inválido.',
    'required'             => 'O campo \':attribute\' é obrigatório.',
    'required_if'          => 'O campo \':attribute\' é obrigatório quando \':other\' é \':value\'.',
    'required_unless'      => 'O campo \':attribute\' é obrigatório exceto se \':other\' está em :values.',
    'required_with'        => 'O campo \':attribute\' é obrigatório quando o campo \':values\' é informado.',
    'required_with_all'    => 'O campo \':attribute\' é obrigatório quando os campos \':values\' são informados.',
    'required_without'     => 'O campo \':attribute\' é obrigatório quando o campo \':values\' não é informado.',
    'required_without_all' => 'O campo \':attribute\' é obrigatório quando nenhum dos campos \':values\' são informados.',
    'required_if_different' => 'O campo \':attribute\' é obrigatório.',
    'required_for_completion' => 'O campo \':attribute\' é obrigatório para concluir essa etapa.',
    'boolean_required_for_completion' => 'O campo \':attribute\' deve ser preenchido com Sim ou Não para concluir essa etapa.',
    'same'                 => 'Os campos \':attribute\' e \':other\' devem ser iguais.',
    'size'                 => [
        'numeric' => 'O campo \':attribute\' deve ser :size.',
        'file'    => 'O campo \':attribute\' deve ter :size kilobytes.',
        'string'  => 'O campo \':attribute\' deve ter :size caracteres.',
        'array'   => 'O campo \':attribute\' deve ter :size ítens.',
    ],
    'string'               => 'O campo \':attribute\' deve ser textual.',
    'timezone'             => 'O campo \':attribute\' deve ser um fuso horário válido.',
    'unique'               => 'O \':attribute\' informado já está em uso.',
    'uploaded'             => 'O arquivo \':attribute\' falhou ao enviar.',
    'url'                  => 'O campo \':attribute\' não é uma URL válida.',

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | Here you may specify custom validation messages for attributes using the
    | convention "attribute.rule" to name the lines. This makes it quick to
    | specify a specific custom language line for a given attribute rule.
    |
    */

    'custom' => [
        'attribute-name' => [
            'rule-name' => 'custom-message',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Attributes
    |--------------------------------------------------------------------------
    |
    | The following language lines are used to swap attribute place-holders
    | with something more reader friendly such as E-Mail Address instead
    | of "email". This simply helps us make messages a little cleaner.
    |
    */

    'attributes' => [
        'name' => 'nome',
        'gender' => 'gênero',
        'race' => 'raça',
        'dob' => 'data de nascimento',
        'rg' => 'RG',
        'cpf' => 'CPF',
        'nis' => 'NIS',
        'cns' => 'CNS',
        'alert_cause_id' => 'motivo do alerta',
        'mother_name' => 'nome da mãe ou responsável',
        'mother_rg' => 'RG da mãe ou responsável',
        'mother_phone' => 'telefone da mãe ou responsável',
        'father_name' => 'nome do pai ou responsável',
        'father_rg' => 'RG do pai ou responsável',
        'father_phone' => 'telefone do pai ou responsável',
        'place_address' => 'endereço',
        'place_cep' => 'CEP',
        'place_reference' => 'referência geográfica',
        'place_neighborhood' => 'bairro',
        'place_city_id' => 'município',
        'place_city_name' => 'município',
        'place_uf' => 'UF',
        'place_phone' => 'telefone',
        'place_mobile' => 'celular',
        'place_lat' => 'latitude do local',
        'place_lng' => 'longitude do local',
        'place_map_region' => 'região',
        'place_map_geocoded_address' => 'endereço no Google Maps',
        'analysis_details' => 'detalhamento da análise',
        'report_date' => 'data do relatório',
        'report_index' => 'índice do relatório',
        'is_child_still_in_school' => 'criança ainda está na escola?',
        'evasion_reason' => 'motivo da evasão',
        'observations' => 'observações',
        'has_been_in_school' => 'criança ainda está na escola?',
        'reason_not_enrolled' => 'motivo',
        'school_last_grade' => 'qual o último ano que frequentou',
        'school_last_year' => 'em qual ano foi cursado o último ano escolar',
        'school_last_id' => 'qual a última escola que frequentou',
        'school_last_name' => 'qual a última escola que frequentou',
        'school_last_status' => 'status do último ano escolar',
        'school_last_age' => 'com qual idade foi cursado o último ano escolar',
        'school_last_address' => 'endereço da última escola cursada',
        'is_working' => 'a criança está trabalhando?',
        'work_activity' => 'qual a atividade econômica',
        'work_activity_other' => 'especifique outra atividade econômica',
        'work_is_paid' => 'o trabalho é remunerado?',
        'work_weekly_hours' => 'horas semanais trabalhadas',
        'parents_has_mother' => 'possui mãe presente?',
        'parents_has_father' => 'possui pai presente?',
        'parents_has_brother' => 'possui irmão presente?',
        'parents_has_grandparents' => 'possui avós presentes?',
        'parents_has_others' => 'possui outros familiares presentes?',
        'parents_who_is_guardian' => 'quem é o responsável?',
        'parents_income' => 'renda familiar',
        'guardian_name' => 'nome do responsável',
        'guardian_rg' => 'RG do responsável',
        'guardian_cpf' => 'CPF do responsável',
        'guardian_dob' => 'data de nascimento do responsável',
        'guardian_phone' => 'telefone do responsável',
        'guardian_race' => 'etnia do responsável',
        'guardian_schooling' => 'escolaridade do responsável',
        'guardian_job' => 'ocupação do responsável',
        'case_cause_ids' => 'quais os motivos de estar fora da escola',
        'handicapped_at_sus' => 'a criança é atendida pelu SUS?',
        'handicapped_reason_not_enrolled' => 'por qual motivo a matrícula não foi realizada',
        'place_kind' => 'tipo de localização',
        'place_is_quilombola' => 'é comunidade quilombola?',

        'place_is_indigena' => 'é comunidade indígena?',
        'place_is_do_campo' => 'é comunidade do campo?',
        'place_is_ribeirinha' => 'é comunidade ribeirinha?',

        'actions_description' => 'atividades realizadas',
        'reinsertion_date' => 'data de (re)matrícula',
        'reinsertion_grade' => 'em qual ano será (re)matriculado',
        'school_id' => 'escola',
        'school_name' => 'escola',
        'school_address' => 'endereço da escola',
        'school_cep' => 'CEP da escola',
        'school_neighborhood' => 'bairro da escola',
        'school_city_id' => 'município da escola',
        'school_city_name' => 'município da escola',
        'school_uf' => 'UF da escola',
        'school_contact_name' => 'nome do contato da gestão da escola',
        'school_contact_email' => 'e-mail do contato da gestão da escola',
        'school_contact_position' => 'cargo do contato da gestão da escola',
        'school_phone' => 'telefone da escola',
        'school_email' => 'e-mail da escola',

        'email' => 'e-mail',
        'password' => 'senha',

        'tenant_id' => 'município na plataforma',
        'city_id' => 'município',
        'group_id' => 'grupo',

        'type' => 'função ocupada no sistema',

        'work_phone' => 'telefone institucional',
        'work_mobile' => 'celular institucional',

        'personal_mobile' => 'celular pessoal',
        'skype_username' => 'Skype',

        'work_address' => 'endereço institucional',
        'work_cep' => 'CEP do endereço institucional',
        'work_neighborhood' => 'bairro do endereço institucional',
        'work_city_id' => 'município do endereço institucional',
        'work_city_name' => 'município do endereço institucional',
        'work_uf' => 'UF do endereço institucional',

        'institution' => 'órgão',
        'position' => 'cargo',

        'is_suspended' => 'está suspenso?',
        'suspended_by' => 'suspenso por',
    ],

];
