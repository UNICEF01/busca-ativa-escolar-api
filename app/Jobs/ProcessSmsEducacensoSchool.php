<?php
/**
 * Created by PhpStorm.
 * User: mansouza
 * Date: 3/14/2019
 * Time: 4:51 PM
 */

namespace BuscaAtivaEscolar\Jobs;


use BuscaAtivaEscolar\SMS\Handlers\Zenvia;

class ProcessSmsEducacensoSchool
{

    private $school;

    /**
     * ProcessSmsEducacensoSchool constructor.
     * @param $school
     */
    public function __construct($school)
    {
        $this->school = $school;
    }

    /**
     * Handles a queued sms
     * @throws \Exception
     */
    public function handle() {
        $zenvia = new Zenvia();
        $zenvia->send('55'.$this->school->school_cell_phone, $this->getMessage());
    }

    private function getMessage()
    {
        return "Por gentileza, acesse o email ".strtolower($this->school->school_email)." e contribua para localizar as criancas e/ou adolescentes fora da escola.";
    }
}