<?php

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class API_Causes_Test extends TestCase {

    public function testAlertCauses() {
        $this->get('/api/v1/static/alert_causes')
	        ->assertResponseStatus(200)
	        ->seeJsonStructure([
	        	'alert_causes' => [
	        		'*' => ['id', 'slug', 'label']
		        ]
	        ]);
    }

	public function testCaseCauses() {
		$this->get('/api/v1/static/case_causes')
			->assertResponseStatus(200)
			->seeJsonStructure([
				'case_causes' => [
					'*' => ['id', 'slug', 'label', 'alert_cause_id']
				]
			]);
	}
}
