<?php
/**
 * busca-ativa-escolar-api
 * UFs.php
 *
 * Copyright (c) LQDI Digital
 * www.lqdi.net - 2016
 *
 * @author Aryel Tupinambá <aryel.tupinamba@lqdi.net>
 *
 * Created at: 27/12/2016, 12:40
 */

namespace BuscaAtivaEscolar\IBGE;


use BuscaAtivaEscolar\Data\StaticObject;

class UF extends StaticObject  {

	protected static $cached = [];

	protected static $data = [
		11 => ['id' => '11', 'region_id' => 1, 'name' => 'Rondônia', 'code' => 'RO', 'slug' => 'ro', 'lat' => '-8.764597', 'lng' => '-63.903943'],
		12 => ['id' => '12', 'region_id' => 1, 'name' => 'Acre', 'code' => 'AC', 'slug' => 'ac', 'lat' => '-9.972463', 'lng' => '-67.812749'],
		13 => ['id' => '13', 'region_id' => 1, 'name' => 'Amazonas', 'code' => 'AM', 'slug' => 'am', 'lat' => '-3.133842', 'lng' => '-60.020165'],
		14 => ['id' => '14', 'region_id' => 1, 'name' => 'Roraima', 'code' => 'RR', 'slug' => 'rr', 'lat' => '2.821734', 'lng' => '-60.672061'],
		15 => ['id' => '15', 'region_id' => 1, 'name' => 'Pará', 'code' => 'PA', 'slug' => 'pa', 'lat' => '-1.452005', 'lng' => '-48.503072'],
		16 => ['id' => '16', 'region_id' => 1, 'name' => 'Amapá', 'code' => 'AP', 'slug' => 'ap', 'lat' => '0.039045', 'lng' => '-51.050099'],
		17 => ['id' => '17', 'region_id' => 1, 'name' => 'Tocantins', 'code' => 'TO', 'slug' => 'to', 'lat' => '-10.184567', 'lng' => '-48.333654'],
		21 => ['id' => '21', 'region_id' => 2, 'name' => 'Maranhão', 'code' => 'MA', 'slug' => 'ma', 'lat' => '-2.532066', 'lng' => '-44.299996'],
		22 => ['id' => '22', 'region_id' => 2, 'name' => 'Piauí', 'code' => 'PI', 'slug' => 'pi', 'lat' => '-5.092628', 'lng' => '-42.810155'],
		23 => ['id' => '23', 'region_id' => 2, 'name' => 'Ceará', 'code' => 'CE', 'slug' => 'ce', 'lat' => '-3.730536', 'lng' => '-38.521777'],
		24 => ['id' => '24', 'region_id' => 2, 'name' => 'Rio Grande do Norte', 'code' => 'RN', 'slug' => 'rn', 'lat' => '-5.786403', 'lng' => '-35.207978'],
		25 => ['id' => '25', 'region_id' => 2, 'name' => 'Paraíba', 'code' => 'PB', 'slug' => 'pb', 'lat' => '-7.120034', 'lng' => '-34.876211'],
		26 => ['id' => '26', 'region_id' => 2, 'name' => 'Pernambuco', 'code' => 'PE', 'slug' => 'pe', 'lat' => '-8.054278', 'lng' => '-34.881256'],
		27 => ['id' => '27', 'region_id' => 2, 'name' => 'Alagoas', 'code' => 'AL', 'slug' => 'al', 'lat' => '-9.667137', 'lng' => '-35.737958'],
		28 => ['id' => '28', 'region_id' => 2, 'name' => 'Sergipe', 'code' => 'SE', 'slug' => 'se', 'lat' => '-10.912647', 'lng' => '-37.053451'],
		29 => ['id' => '29', 'region_id' => 2, 'name' => 'Bahia', 'code' => 'BA', 'slug' => 'ba', 'lat' => '-12.970382', 'lng' => '-38.512382'],
		31 => ['id' => '31', 'region_id' => 3, 'name' => 'Minas Gerais', 'code' => 'MG', 'slug' => 'mg', 'lat' => '-19.918339', 'lng' => '-43.940102'],
		32 => ['id' => '32', 'region_id' => 3, 'name' => 'Espirito Santo', 'code' => 'ES', 'slug' => 'es', 'lat' => '-20.319933', 'lng' => '-40.336296'],
		33 => ['id' => '33', 'region_id' => 3, 'name' => 'Rio de Janeiro', 'code' => 'RJ', 'slug' => 'rj', 'lat' => '-22.908892', 'lng' => '-43.177138'],
		35 => ['id' => '35', 'region_id' => 3, 'name' => 'São Paulo', 'code' => 'SP', 'slug' => 'sp', 'lat' => '-23.550483', 'lng' => '-46.633106'],
		41 => ['id' => '41', 'region_id' => 4, 'name' => 'Paraná', 'code' => 'PR', 'slug' => 'pr', 'lat' => '-25.433171', 'lng' => '-49.27147'],
		42 => ['id' => '42', 'region_id' => 4, 'name' => 'Santa Catarina', 'code' => 'SC', 'slug' => 'sc', 'lat' => '-27.593237', 'lng' => '-48.543736'],
		43 => ['id' => '43', 'region_id' => 4, 'name' => 'Rio Grande do Sul', 'code' => 'RS', 'slug' => 'rs', 'lat' => '-30.033914', 'lng' => '-51.229154'],
		50 => ['id' => '50', 'region_id' => 5, 'name' => 'Mato Grosso do Sul', 'code' => 'MS', 'slug' => 'ms', 'lat' => '-20.461719', 'lng' => '-54.612237'],
		51 => ['id' => '51', 'region_id' => 5, 'name' => 'Mato Grosso', 'code' => 'MT', 'slug' => 'mt', 'lat' => '-15.598917', 'lng' => '-56.094894'],
		52 => ['id' => '52', 'region_id' => 5, 'name' => 'Goiás', 'code' => 'GO', 'slug' => 'go', 'lat' => '-16.67992', 'lng' => '-49.255032'],
		53 => ['id' => '53', 'region_id' => 5, 'name' => 'Distrito Federal', 'code' => 'DF', 'slug' => 'df', 'lat' => '-15.79983', 'lng' => '-47.863711'],
	];

	protected static $indexes = [
		'code' => [
			'RO' => 11,
			'AC' => 12,
			'AM' => 13,
			'RR' => 14,
			'PA' => 15,
			'AP' => 16,
			'TO' => 17,
			'MA' => 21,
			'PI' => 22,
			'CE' => 23,
			'RN' => 24,
			'PB' => 25,
			'PE' => 26,
			'AL' => 27,
			'SE' => 28,
			'BA' => 29,
			'MG' => 31,
			'ES' => 32,
			'RJ' => 33,
			'SP' => 35,
			'PR' => 41,
			'SC' => 42,
			'RS' => 43,
			'MS' => 50,
			'MT' => 51,
			'GO' => 52,
			'DF' => 53,
		]
	];

	/**
	 * Gets an UF by it's short code
	 * @param string $code The short UF code (eg.: SP)
	 * @return UF
	 */
	public static function getByCode($code) {
		return self::getByIndex('code', $code);
	}

	/**
	 * @var integer Unique numeric ID for the UF
	 */
	public $id;

	/**
	 * @var string UF name (unicode)
	 */
	public $name;

	/**
	 * @var string The UF short code (two characters, uppercase)
	 */
	public $code;

	/**
	 * @var integer The ID of the region this UF is contained by
	 */
	public $region_id;

	/**
	 * @var string The latitude of the state's capital
	 */
	public $lat;

	/**
	 * @var string The longitude of the state's capital
	 */
	public $lng;

	/**
	 * Gets the region that contains the UF
	 * @return Region
	 */
	public function getRegion() {
		return Region::getByID($this->region_id);
	}

	/**
	 * Gets the lat/lng coordinates of the UF on an associative array
	 * @return array
	 */
	public function getCoordinates() {
		return ['lat' => $this->lat, 'lng' => $this->lng];
	}

}