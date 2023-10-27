<?php

/**
 * Created by PhpStorm.
 * User: manoelfilho
 * Date: 2019-03-10
 * Time: 15:02
 */

namespace BuscaAtivaEscolar\EmailTypes;

use BuscaAtivaEscolar\EmailJob;
use BuscaAtivaEscolar\School;
use Illuminate\Support\Facades\Mail;
use BuscaAtivaEscolar\Mail\SchoolEducacensoNotification;
use Event;

class SchoolEducacensoEmail implements SendEmail
{

    const TYPE = "school_educacenso_email";

    /**
     * @var int The number of JOB
     */
    public $job_id;

    /**
     * @var int The School
     */
    public $school;

    /**
     * @var string The id of user that is identified as the creator of emails
     */
    private $user_id;

    /**
     * @var string The email of school that is identified as the receiver of email
     */
    private $school_email;

    /**
     * @var string The email of user that is identified as the receiver of email
     */
    private $email_user;


    /**
     * Handles the sending of Educacenso's Emails
     * @param EmailJob $job
     * @throws \Exception
     */
    public function handle(EmailJob $job)
    {

        $this->job_id = $job->id;
        $this->user_id = $job->user_id;
        $this->school_email = $job->school_email;
        $this->email_user = $job->email_user;
        $this->school = School::findOrFail($job->school_id);


        try {
            $message = new SchoolEducacensoNotification($this->school, $this->job_id);

            Mail::to($this->school_email)
                ->cc($this->email_user)
                ->send($message);
        } catch (\Exception $ex) {

            throw $ex;
        }
    }
}
