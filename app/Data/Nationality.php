<?php

namespace BuscaAtivaEscolar\Data;

class Nationality extends StaticObject
{

    protected static $data = [
        1 => ['id' => 1, 'slug' => 'afeganistao', 'label' => 'Afeganistão'],
        2 => ['id' => 2, 'slug' => 'africa_do_sul', 'label' => 'África do Sul'],
        3 => ['id' => 3, 'slug' => 'albania', 'label' => 'Albânia'],
        4 => ['id' => 4, 'slug' => 'alemanha', 'label' => 'Alemanha'],
        5 => ['id' => 5, 'slug' => 'andorra', 'label' => 'Andorra'],
        6 => ['id' => 6, 'slug' => 'angola', 'label' => 'Angola'],
        7 => ['id' => 7, 'slug' => 'antiga_e_barbuda', 'label' => 'Antiga e Barbuda'],
        8 => ['id' => 8, 'slug' => 'arabia_saudita', 'label' => 'Arábia Saudita'],
        9 => ['id' => 9, 'slug' => 'argelia', 'label' => 'Argélia'],
        10 => ['id' => 10, 'slug' => 'argentina', 'label' => 'Argentina'],
        11 => ['id' => 11, 'slug' => 'armenia', 'label' => 'Arménia'],
        12 => ['id' => 12, 'slug' => 'australia', 'label' => 'Austrália'],
        13 => ['id' => 13, 'slug' => 'austria', 'label' => 'Áustria'],
        14 => ['id' => 14, 'slug' => 'azerbaijao', 'label' => 'Azerbaijão'],
        15 => ['id' => 15, 'slug' => 'bahamas', 'label' => 'Bahamas'],
        16 => ['id' => 16, 'slug' => 'bangladexe', 'label' => 'Bangladexe'],
        17 => ['id' => 17, 'slug' => 'barbados', 'label' => 'Barbados'],
        18 => ['id' => 18, 'slug' => 'barem', 'label' => 'Barém'],
        19 => ['id' => 19, 'slug' => 'belgica', 'label' => 'Bélgica'],
        20 => ['id' => 20, 'slug' => 'belize', 'label' => 'Belize'],
        21 => ['id' => 21, 'slug' => 'benim', 'label' => 'Benim'],
        22 => ['id' => 22, 'slug' => 'bielorrussia', 'label' => 'Bielorrússia'],
        23 => ['id' => 23, 'slug' => 'bolivia', 'label' => 'Bolívia'],
        24 => ['id' => 24, 'slug' => 'bosnia_e_herzegovina', 'label' => 'Bósnia e Herzegovina'],
        25 => ['id' => 25, 'slug' => 'botsuana', 'label' => 'Botsuana'],
        26 => ['id' => 26, 'slug' => 'brasil', 'label' => 'Brasil'],
        27 => ['id' => 27, 'slug' => 'brunei', 'label' => 'Brunei'],
        28 => ['id' => 28, 'slug' => 'bulgaria', 'label' => 'Bulgária'],
        29 => ['id' => 29, 'slug' => 'burquina_faso', 'label' => 'Burquina Faso'],
        30 => ['id' => 30, 'slug' => 'burundi', 'label' => 'Burúndi'],
        31 => ['id' => 31, 'slug' => 'butao', 'label' => 'Butão'],
        32 => ['id' => 32, 'slug' => 'cabo_verde', 'label' => 'Cabo Verde'],
        33 => ['id' => 33, 'slug' => 'camaroes', 'label' => 'Camarões'],
        34 => ['id' => 34, 'slug' => 'camboja', 'label' => 'Camboja'],
        35 => ['id' => 35, 'slug' => 'canada', 'label' => 'Canadá'],
        36 => ['id' => 36, 'slug' => 'catar', 'label' => 'Catar'],
        37 => ['id' => 37, 'slug' => 'cazaquistao', 'label' => 'Cazaquistão'],
        38 => ['id' => 38, 'slug' => 'chade', 'label' => 'Chade'],
        39 => ['id' => 39, 'slug' => 'chile', 'label' => 'Chile'],
        40 => ['id' => 40, 'slug' => 'china', 'label' => 'China'],
        41 => ['id' => 41, 'slug' => 'chipre', 'label' => 'Chipre'],
        42 => ['id' => 42, 'slug' => 'colombia', 'label' => 'Colômbia'],
        43 => ['id' => 43, 'slug' => 'comores', 'label' => 'Comores'],
        44 => ['id' => 44, 'slug' => 'congo-brazzaville', 'label' => 'Congo-Brazzaville'],
        45 => ['id' => 45, 'slug' => 'coreia_do_norte', 'label' => 'Coreia do Norte'],
        46 => ['id' => 46, 'slug' => 'coreia_do_sul', 'label' => 'Coreia do Sul'],
        47 => ['id' => 47, 'slug' => 'cosovo', 'label' => 'Cosovo'],
        48 => ['id' => 48, 'slug' => 'costa_do_marfim', 'label' => 'Costa do Marfim'],
        49 => ['id' => 49, 'slug' => 'costa_rica', 'label' => 'Costa Rica'],
        50 => ['id' => 50, 'slug' => 'croacia', 'label' => 'Croácia'],
        51 => ['id' => 51, 'slug' => 'cuaite', 'label' => 'Cuaite'],
        52 => ['id' => 52, 'slug' => 'cuba', 'label' => 'Cuba'],
        53 => ['id' => 53, 'slug' => 'dinamarca', 'label' => 'Dinamarca'],
        54 => ['id' => 54, 'slug' => 'dominica', 'label' => 'Dominica'],
        55 => ['id' => 55, 'slug' => 'egito', 'label' => 'Egito'],
        56 => ['id' => 56, 'slug' => 'emirados_arabes_unidos', 'label' => 'Emirados Árabes Unidos'],
        57 => ['id' => 57, 'slug' => 'equador', 'label' => 'Equador'],
        58 => ['id' => 58, 'slug' => 'eritreia', 'label' => 'Eritreia'],
        59 => ['id' => 59, 'slug' => 'eslovaquia', 'label' => 'Eslováquia'],
        60 => ['id' => 60, 'slug' => 'eslovenia', 'label' => 'Eslovénia'],
        61 => ['id' => 61, 'slug' => 'espanha', 'label' => 'Espanha'],
        62 => ['id' => 62, 'slug' => 'essuatini', 'label' => 'Essuatíni'],
        63 => ['id' => 63, 'slug' => 'estado_da_palestina', 'label' => 'Estado da Palestina'],
        64 => ['id' => 64, 'slug' => 'estados_unidos', 'label' => 'Estados Unidos'],
        65 => ['id' => 65, 'slug' => 'estonia', 'label' => 'Estónia'],
        66 => ['id' => 66, 'slug' => 'etiopia', 'label' => 'Etiópia'],
        67 => ['id' => 67, 'slug' => 'fiji', 'label' => 'Fiji'],
        68 => ['id' => 68, 'slug' => 'filipinas', 'label' => 'Filipinas'],
        69 => ['id' => 69, 'slug' => 'finlandia', 'label' => 'Finlândia'],
        70 => ['id' => 70, 'slug' => 'franca', 'label' => 'França'],
        71 => ['id' => 71, 'slug' => 'gabao', 'label' => 'Gabão'],
        72 => ['id' => 72, 'slug' => 'gambia', 'label' => 'Gâmbia'],
        73 => ['id' => 73, 'slug' => 'gana', 'label' => 'Gana'],
        74 => ['id' => 74, 'slug' => 'georgia', 'label' => 'Geórgia'],
        75 => ['id' => 75, 'slug' => 'granada', 'label' => 'Granada'],
        76 => ['id' => 76, 'slug' => 'grecia', 'label' => 'Grécia'],
        77 => ['id' => 77, 'slug' => 'guatemala', 'label' => 'Guatemala'],
        78 => ['id' => 78, 'slug' => 'guiana', 'label' => 'Guiana'],
        79 => ['id' => 79, 'slug' => 'guine', 'label' => 'Guiné'],
        80 => ['id' => 80, 'slug' => 'guine_equatorial', 'label' => 'Guiné Equatorial'],
        81 => ['id' => 81, 'slug' => 'guine-bissau', 'label' => 'Guiné-Bissau'],
        82 => ['id' => 82, 'slug' => 'haiti', 'label' => 'Haiti'],
        83 => ['id' => 83, 'slug' => 'honduras', 'label' => 'Honduras'],
        84 => ['id' => 84, 'slug' => 'hungria', 'label' => 'Hungria'],
        85 => ['id' => 85, 'slug' => 'iemen', 'label' => 'Iémen'],
        86 => ['id' => 86, 'slug' => 'ilhas_marechal', 'label' => 'Ilhas Marechal'],
        87 => ['id' => 87, 'slug' => 'india', 'label' => 'Índia'],
        88 => ['id' => 88, 'slug' => 'indonesia', 'label' => 'Indonésia'],
        89 => ['id' => 89, 'slug' => 'irao', 'label' => 'Irão'],
        90 => ['id' => 90, 'slug' => 'iraque', 'label' => 'Iraque'],
        91 => ['id' => 91, 'slug' => 'irlanda', 'label' => 'Irlanda'],
        92 => ['id' => 92, 'slug' => 'islandia', 'label' => 'Islândia'],
        93 => ['id' => 93, 'slug' => 'israel', 'label' => 'Israel'],
        94 => ['id' => 94, 'slug' => 'italia', 'label' => 'Itália'],
        95 => ['id' => 95, 'slug' => 'jamaica', 'label' => 'Jamaica'],
        96 => ['id' => 96, 'slug' => 'japao', 'label' => 'Japão'],
        97 => ['id' => 97, 'slug' => 'jibuti', 'label' => 'Jibuti'],
        98 => ['id' => 98, 'slug' => 'jordania', 'label' => 'Jordânia'],
        99 => ['id' => 99, 'slug' => 'laus', 'label' => 'Laus'],
        100 => ['id' => 100, 'slug' => 'lesoto', 'label' => 'Lesoto'],
        101 => ['id' => 101, 'slug' => 'letonia', 'label' => 'Letónia'],
        102 => ['id' => 102, 'slug' => 'libano', 'label' => 'Líbano'],
        103 => ['id' => 103, 'slug' => 'liberia', 'label' => 'Libéria'],
        104 => ['id' => 104, 'slug' => 'libia', 'label' => 'Líbia'],
        105 => ['id' => 105, 'slug' => 'listenstaine', 'label' => 'Listenstaine'],
        106 => ['id' => 106, 'slug' => 'lituania', 'label' => 'Lituânia'],
        107 => ['id' => 107, 'slug' => 'luxemburgo', 'label' => 'Luxemburgo'],
        108 => ['id' => 108, 'slug' => 'macedonia_do_norte', 'label' => 'Macedónia do Norte'],
        109 => ['id' => 109, 'slug' => 'madagascar', 'label' => 'Madagáscar'],
        110 => ['id' => 110, 'slug' => 'malasia', 'label' => 'Malásia'],
        111 => ['id' => 111, 'slug' => 'malaui', 'label' => 'Maláui'],
        112 => ['id' => 112, 'slug' => 'maldivas', 'label' => 'Maldivas'],
        113 => ['id' => 113, 'slug' => 'mali', 'label' => 'Mali'],
        114 => ['id' => 114, 'slug' => 'malta', 'label' => 'Malta'],
        115 => ['id' => 115, 'slug' => 'marrocos', 'label' => 'Marrocos'],
        116 => ['id' => 116, 'slug' => 'mauricia', 'label' => 'Maurícia'],
        117 => ['id' => 117, 'slug' => 'mauritania', 'label' => 'Mauritânia'],
        118 => ['id' => 118, 'slug' => 'mexico', 'label' => 'México'],
        119 => ['id' => 119, 'slug' => 'mianmar', 'label' => 'Mianmar'],
        120 => ['id' => 120, 'slug' => 'micronesia', 'label' => 'Micronésia'],
        121 => ['id' => 121, 'slug' => 'mocambique', 'label' => 'Moçambique'],
        122 => ['id' => 122, 'slug' => 'moldavia', 'label' => 'Moldávia'],
        123 => ['id' => 123, 'slug' => 'monaco', 'label' => 'Mónaco'],
        124 => ['id' => 124, 'slug' => 'mongolia', 'label' => 'Mongólia'],
        125 => ['id' => 125, 'slug' => 'montenegro', 'label' => 'Montenegro'],
        126 => ['id' => 126, 'slug' => 'namibia', 'label' => 'Namíbia'],
        127 => ['id' => 127, 'slug' => 'nauru', 'label' => 'Nauru'],
        128 => ['id' => 128, 'slug' => 'nepal', 'label' => 'Nepal'],
        129 => ['id' => 129, 'slug' => 'nicaragua', 'label' => 'Nicarágua'],
        130 => ['id' => 130, 'slug' => 'niger', 'label' => 'Níger'],
        131 => ['id' => 131, 'slug' => 'nigeria', 'label' => 'Nigéria'],
        132 => ['id' => 132, 'slug' => 'noruega', 'label' => 'Noruega'],
        133 => ['id' => 133, 'slug' => 'nova_zelandia', 'label' => 'Nova Zelândia'],
        134 => ['id' => 134, 'slug' => 'oma', 'label' => 'Omã'],
        135 => ['id' => 135, 'slug' => 'paises_baixos', 'label' => 'Países Baixos'],
        136 => ['id' => 136, 'slug' => 'palau', 'label' => 'Palau'],
        137 => ['id' => 137, 'slug' => 'panama', 'label' => 'Panamá'],
        138 => ['id' => 138, 'slug' => 'papua_nova_guine', 'label' => 'Papua Nova Guiné'],
        139 => ['id' => 139, 'slug' => 'paquistao', 'label' => 'Paquistão'],
        140 => ['id' => 140, 'slug' => 'paraguai', 'label' => 'Paraguai'],
        141 => ['id' => 141, 'slug' => 'peru', 'label' => 'Peru'],
        142 => ['id' => 142, 'slug' => 'polonia', 'label' => 'Polónia'],
        143 => ['id' => 143, 'slug' => 'portugal', 'label' => 'Portugal'],
        144 => ['id' => 144, 'slug' => 'quenia', 'label' => 'Quénia'],
        145 => ['id' => 145, 'slug' => 'quirguistao', 'label' => 'Quirguistão'],
        146 => ['id' => 146, 'slug' => 'quiribati', 'label' => 'Quiribáti'],
        147 => ['id' => 147, 'slug' => 'reino_unido', 'label' => 'Reino Unido'],
        148 => ['id' => 148, 'slug' => 'republica_centro-africana', 'label' => 'República Centro-Africana'],
        149 => ['id' => 149, 'slug' => 'republica_checa', 'label' => 'República Checa'],
        150 => ['id' => 150, 'slug' => 'republica_democratica_do_congo', 'label' => 'República Democrática do Congo'],
        151 => ['id' => 151, 'slug' => 'republica_dominicana', 'label' => 'República Dominicana'],
        152 => ['id' => 152, 'slug' => 'romenia', 'label' => 'Roménia'],
        153 => ['id' => 153, 'slug' => 'ruanda', 'label' => 'Ruanda'],
        154 => ['id' => 154, 'slug' => 'russia', 'label' => 'Rússia'],
        155 => ['id' => 155, 'slug' => 'salomao', 'label' => 'Salomão'],
        156 => ['id' => 156, 'slug' => 'salvador', 'label' => 'Salvador'],
        157 => ['id' => 157, 'slug' => 'samoa', 'label' => 'Samoa'],
        158 => ['id' => 158, 'slug' => 'santa_lucia', 'label' => 'Santa Lúcia'],
        159 => ['id' => 159, 'slug' => 'sao_cristovao_e_neves', 'label' => 'São Cristóvão e Neves'],
        160 => ['id' => 160, 'slug' => 'sao_marinho', 'label' => 'São Marinho'],
        161 => ['id' => 161, 'slug' => 'sao_tome_e_principe', 'label' => 'São Tomé e Príncipe'],
        162 => ['id' => 162, 'slug' => 'sao_vicente_e_granadinas', 'label' => 'São Vicente e Granadinas'],
        163 => ['id' => 163, 'slug' => 'seicheles', 'label' => 'Seicheles'],
        164 => ['id' => 164, 'slug' => 'senegal', 'label' => 'Senegal'],
        165 => ['id' => 165, 'slug' => 'serra_leoa', 'label' => 'Serra Leoa'],
        166 => ['id' => 166, 'slug' => 'servia', 'label' => 'Sérvia'],
        167 => ['id' => 167, 'slug' => 'singapura', 'label' => 'Singapura'],
        168 => ['id' => 168, 'slug' => 'siria', 'label' => 'Síria'],
        169 => ['id' => 169, 'slug' => 'somalia', 'label' => 'Somália'],
        170 => ['id' => 170, 'slug' => 'sri_lanca', 'label' => 'Sri Lanca'],
        171 => ['id' => 171, 'slug' => 'sudao', 'label' => 'Sudão'],
        172 => ['id' => 172, 'slug' => 'sudao_do_sul', 'label' => 'Sudão do Sul'],
        173 => ['id' => 173, 'slug' => 'suecia', 'label' => 'Suécia'],
        174 => ['id' => 174, 'slug' => 'suica', 'label' => 'Suíça'],
        175 => ['id' => 175, 'slug' => 'suriname', 'label' => 'Suriname'],
        176 => ['id' => 176, 'slug' => 'tailandia', 'label' => 'Tailândia'],
        177 => ['id' => 177, 'slug' => 'taiua', 'label' => 'Taiuã'],
        178 => ['id' => 178, 'slug' => 'tajiquistao', 'label' => 'Tajiquistão'],
        179 => ['id' => 179, 'slug' => 'tanzania', 'label' => 'Tanzânia'],
        180 => ['id' => 180, 'slug' => 'timor-leste', 'label' => 'Timor-Leste'],
        181 => ['id' => 181, 'slug' => 'togo', 'label' => 'Togo'],
        182 => ['id' => 182, 'slug' => 'tonga', 'label' => 'Tonga'],
        183 => ['id' => 183, 'slug' => 'trindade_e_tobago', 'label' => 'Trindade e Tobago'],
        184 => ['id' => 184, 'slug' => 'tunisia', 'label' => 'Tunísia'],
        185 => ['id' => 185, 'slug' => 'turcomenistao', 'label' => 'Turcomenistão'],
        186 => ['id' => 186, 'slug' => 'turquia', 'label' => 'Turquia'],
        187 => ['id' => 187, 'slug' => 'tuvalu', 'label' => 'Tuvalu'],
        188 => ['id' => 188, 'slug' => 'ucrania', 'label' => 'Ucrânia'],
        189 => ['id' => 189, 'slug' => 'uganda', 'label' => 'Uganda'],
        190 => ['id' => 190, 'slug' => 'uruguai', 'label' => 'Uruguai'],
        191 => ['id' => 191, 'slug' => 'usbequistao', 'label' => 'Usbequistão'],
        192 => ['id' => 192, 'slug' => 'vanuatu', 'label' => 'Vanuatu'],
        193 => ['id' => 193, 'slug' => 'vaticano', 'label' => 'Vaticano'],
        194 => ['id' => 194, 'slug' => 'venezuela', 'label' => 'Venezuela'],
        195 => ['id' => 195, 'slug' => 'vietname', 'label' => 'Vietname'],
        196 => ['id' => 196, 'slug' => 'zambia', 'label' => 'Zâmbia'],
        197 => ['id' => 197, 'slug' => 'zimbabue', 'label' => 'Zimbábue'],
    ];

    protected static $indexes = [
        'slug' => [
            'afeganistao'  => 1,
            'africa_do_sul'  => 2,
            'albania'  => 3,
            'alemanha'  => 4,
            'andorra'  => 5,
            'angola'  => 6,
            'antiga_e_barbuda'  => 7,
            'arabia_saudita'  => 8,
            'argelia'  => 9,
            'argentina'  => 10,
            'armenia'  => 11,
            'australia'  => 12,
            'austria'  => 13,
            'azerbaijao'  => 14,
            'bahamas'  => 15,
            'bangladexe'  => 16,
            'barbados'  => 17,
            'barem'  => 18,
            'belgica'  => 19,
            'belize'  => 20,
            'benim'  => 21,
            'bielorrussia'  => 22,
            'bolivia'  => 23,
            'bosnia_e_herzegovina'  => 24,
            'botsuana'  => 25,
            'brasil'  => 26,
            'brunei'  => 27,
            'bulgaria'  => 28,
            'burquina_faso'  => 29,
            'burundi'  => 30,
            'butao'  => 31,
            'cabo_verde'  => 32,
            'camaroes'  => 33,
            'camboja'  => 34,
            'canada'  => 35,
            'catar'  => 36,
            'cazaquistao'  => 37,
            'chade'  => 38,
            'chile'  => 39,
            'china'  => 40,
            'chipre'  => 41,
            'colombia'  => 42,
            'comores'  => 43,
            'congo-brazzaville'  => 44,
            'coreia_do_norte'  => 45,
            'coreia_do_sul'  => 46,
            'cosovo'  => 47,
            'costa_do_marfim'  => 48,
            'costa_rica'  => 49,
            'croacia'  => 50,
            'cuaite'  => 51,
            'cuba'  => 52,
            'dinamarca'  => 53,
            'dominica'  => 54,
            'egito'  => 55,
            'emirados_arabes_unidos'  => 56,
            'equador'  => 57,
            'eritreia'  => 58,
            'eslovaquia'  => 59,
            'eslovenia'  => 60,
            'espanha'  => 61,
            'essuatini'  => 62,
            'estado_da_palestina'  => 63,
            'estados_unidos'  => 64,
            'estonia'  => 65,
            'etiopia'  => 66,
            'fiji'  => 67,
            'filipinas'  => 68,
            'finlandia'  => 69,
            'franca'  => 70,
            'gabao'  => 71,
            'gambia'  => 72,
            'gana'  => 73,
            'georgia'  => 74,
            'granada'  => 75,
            'grecia'  => 76,
            'guatemala'  => 77,
            'guiana'  => 78,
            'guine'  => 79,
            'guine_equatorial'  => 80,
            'guine-bissau'  => 81,
            'haiti'  => 82,
            'honduras'  => 83,
            'hungria'  => 84,
            'iemen'  => 85,
            'ilhas_marechal'  => 86,
            'india'  => 87,
            'indonesia'  => 88,
            'irao'  => 89,
            'iraque'  => 90,
            'irlanda'  => 91,
            'islandia'  => 92,
            'israel'  => 93,
            'italia'  => 94,
            'jamaica'  => 95,
            'japao'  => 96,
            'jibuti'  => 97,
            'jordania'  => 98,
            'laus'  => 99,
            'lesoto'  => 100,
            'letonia'  => 101,
            'libano'  => 102,
            'liberia'  => 103,
            'libia'  => 104,
            'listenstaine'  => 105,
            'lituania'  => 106,
            'luxemburgo'  => 107,
            'macedonia_do_norte'  => 108,
            'madagascar'  => 109,
            'malasia'  => 110,
            'malaui'  => 111,
            'maldivas'  => 112,
            'mali'  => 113,
            'malta'  => 114,
            'marrocos'  => 115,
            'mauricia'  => 116,
            'mauritania'  => 117,
            'mexico'  => 118,
            'mianmar'  => 119,
            'micronesia'  => 120,
            'mocambique'  => 121,
            'moldavia'  => 122,
            'monaco'  => 123,
            'mongolia'  => 124,
            'montenegro'  => 125,
            'namibia'  => 126,
            'nauru'  => 127,
            'nepal'  => 128,
            'nicaragua'  => 129,
            'niger'  => 130,
            'nigeria'  => 131,
            'noruega'  => 132,
            'nova_zelandia'  => 133,
            'oma'  => 134,
            'paises_baixos'  => 135,
            'palau'  => 136,
            'panama'  => 137,
            'papua_nova_guine'  => 138,
            'paquistao'  => 139,
            'paraguai'  => 140,
            'peru'  => 141,
            'polonia'  => 142,
            'portugal'  => 143,
            'quenia'  => 144,
            'quirguistao'  => 145,
            'quiribati'  => 146,
            'reino_unido'  => 147,
            'republica_centro-africana'  => 148,
            'republica_checa'  => 149,
            'republica_democratica_do_congo'  => 150,
            'republica_dominicana'  => 151,
            'romenia'  => 152,
            'ruanda'  => 153,
            'russia'  => 154,
            'salomao'  => 155,
            'salvador'  => 156,
            'samoa'  => 157,
            'santa_lucia'  => 158,
            'sao_cristovao_e_neves'  => 159,
            'sao_marinho'  => 160,
            'sao_tome_e_principe'  => 161,
            'sao_vicente_e_granadinas'  => 162,
            'seicheles'  => 163,
            'senegal'  => 164,
            'serra_leoa'  => 165,
            'servia'  => 166,
            'singapura'  => 167,
            'siria'  => 168,
            'somalia'  => 169,
            'sri_lanca'  => 170,
            'sudao'  => 171,
            'sudao_do_sul'  => 172,
            'suecia'  => 173,
            'suica'  => 174,
            'suriname'  => 175,
            'tailandia'  => 176,
            'taiua'  => 177,
            'tajiquistao'  => 178,
            'tanzania'  => 179,
            'timor-leste'  => 180,
            'togo'  => 181,
            'tonga'  => 182,
            'trindade_e_tobago'  => 183,
            'tunisia'  => 184,
            'turcomenistao'  => 185,
            'turquia'  => 186,
            'tuvalu'  => 187,
            'ucrania'  => 188,
            'uganda'  => 189,
            'uruguai'  => 190,
            'usbequistao'  => 191,
            'vanuatu'  => 192,
            'vaticano'  => 193,
            'venezuela'  => 194,
            'vietname'  => 195,
            'zambia'  => 196,
            'zimbabue'  => 197,
        ]
    ];

    /**
     * @var string The nationality ID
     */
    public $id;

    /**
     * @var string The nationality slug
     */
    public $slug;

    /**
     * @var string The nationality human-readable name
     */
    public $label;

    /**
     * Gets a race by its slug
     * @param string $slug
     * @return Race
     */
    public static function getBySlug($slug)
    {
        return self::getByIndex('slug', $slug);
    }
}
