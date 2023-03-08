<?php

namespace BuscaAtivaEscolar\Console\Commands;

use BuscaAtivaEscolar\Mail\Seal;
use Illuminate\Console\Command;
use Mail;

class SendEmailSelo extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'maintenance:send_mail_seal';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = "Send email with seal's csv file.";

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $message = new Seal();
        $emails = ['zlauletta@unicef.org',
                   'daranha@unicef.org',
                   'dmagalhaes@unicef.org',
                   'lcortellazzi@unicef.org'];
        Mail::to($emails)->send($message);
    }
}
