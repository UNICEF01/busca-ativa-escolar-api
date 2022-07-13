<?php
/**
 * Created by PhpStorm.
 * User: manoelfilho
 * Date: 2019-03-10
 * Time: 13:49
 */

namespace BuscaAtivaEscolar\EmailTypes;

use BuscaAtivaEscolar\EmailJob;

interface SendEmail
{
    /**
     * @param EmailJob $job
     * @return mixed
     */
    public function handle(EmailJob $job);

}