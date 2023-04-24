<?php

namespace BuscaAtivaEscolar\Console\Commands;

use BuscaAtivaEscolar\Mail\MunicipalWeeklyReport;
use Illuminate\Console\Command;
use Mail;

class SendEmailMunicipal extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'maintenance:send_mail_municipal';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = "Send email with municipal weekly report csv file.";

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
        $message = new MunicipalWeeklyReport();
        $emails = [
            'daranha@unicef.org',
            'dmagalhaes@unicef.org'
        ];
        Mail::to($emails)->send($message);
    }
}