<?php

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class API_Cities_Test extends TestCase {

	public function testIndex() {
		$this->get('/api/v1/cities')
			->assertResponseStatus(200)
			->seeJsonStructure([
				'per_page',
				'current_page',
				'next_page_url',
				'prev_page_url',
				'data' => [
					'*' => ['id', 'uf', 'region', 'name']
				]
			]);
	}

	public function testShow() {
		$cities = $this->get('/api/v1/cities')->decodeResponseJson();
		$city = array_pop($cities['data']);

		$this->get('/api/v1/cities/' . $city['id'])
			->assertResponseStatus(200)
			->seeJsonStructure([
				'id', 'uf', 'region', 'name', 'ibge_city_id', 'ibge_uf_id'
			]);
	}
}
