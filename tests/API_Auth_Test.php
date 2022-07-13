<?php

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class Test_API_Auth extends TestCase {

	public function testAuthToken() {
		$this->post('/api/auth/token', ['email' => 'dev@lqdi.net', 'password' => 'demo']);
		$this->assertResponseStatus(200);
		$this->seeJsonStructure(['token']);
	}

	public function testAuthIdentity() {

		$payload = $this->post('/api/auth/token', ['email' => 'dev@lqdi.net', 'password' => 'demo'])->decodeResponseJson();
		$this->assertArrayHasKey('token', $payload);
		$jwt = $payload['token'];

		$this
			->get('/api/auth/identity', ['Authorization' => "Bearer {$jwt}"])
			->seeJsonStructure([
				'user' => ['id', 'name', 'email', 'type', 'tenant_id', 'city_id']
			]);
	}
}
