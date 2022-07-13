<?php
/**
 * busca-ativa-escolar-api
 * StaticObject.php
 *
 * Copyright (c) LQDI Digital
 * www.lqdi.net - 2016
 *
 * @author Aryel Tupinambá <aryel.tupinamba@lqdi.net>
 *
 * Created at: 28/12/2016, 13:21
 */

namespace BuscaAtivaEscolar\Data;

use Illuminate\Support\Arr;

abstract class StaticObject {

	protected static $cached = [];
	protected static $data = [];
	protected static $indexes = [];

	/**
	 * StaticObject constructor.
	 * Assigns all fields in $data to object properties
	 *
	 * @param array $data
	 */
	public function __construct(array $data) {
		foreach($data as $k => $v) {
			if(!property_exists($this, $k)) continue;
			$this->{$k} = $v;
		}
	}

	/**
	 * Gets a region by it's unique ID
	 * @param integer $id The region ID
	 * @return static
	 * @throws \Exception if ID is invalid
	 */
	public static function getByID($id) {
		if(isset(static::$cached[$id])) {
			return static::$cached[$id];
		}

		if(!isset(static::$data[$id])) {
			throw new \Exception("Invalid object ID: {$id}");
		}

		return static::$cached[$id] = new static(static::$data[$id]);
	}

	/**
	 * Gets an object by it's indexed property
	 * @param string $index The index name
	 * @param string $value The index property value
	 * @return static
	 */
	public static function getByIndex($index, $value) {
		if(!isset(static::$indexes[$index])) return null;
		if(!isset(static::$indexes[$index][$value])) return null;

		$id = static::$indexes[$index][$value];

		return static::getByID($id);
	}

	/**
	 * Returns a collection of all objects, indexed by their IDs
	 * @return static[]
	 */
	public static function getAll() {
		foreach(static::$data as $k => $v) {
			static::getByID($k);
		}

		return static::$cached;
	}

	/**
	 * Returns an associative array with the root data of all objects, indexed by their IDs
	 * @return array
	 */
	public static function getAllAsArray() {
		return static::$data;
	}

	/**
	 * Returns an associative array with the root data of all objects, indexed by their codes
	 * @return array
	 */
	public static function getAllByCode() {
		return collect(static::$data)->keyBy('code')->toArray();
	}

	/**
	 * Gets the validation mask for all possible values in a key
	 * @param string $key The key to build the mask with (default: slug)
	 * @return string
	 */
	public static function getSlugValidationMask($key =  "slug") {
		return "in:" . join(",", Arr::pluck(static::$data, $key));
	}

}