<?php

namespace BuscaAtivaEscolar\Console\Commands;

use BuscaAtivaEscolar\Mail\Lgpd;
use Illuminate\Console\Command;
use Mail;

class SendEmailLgpd extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'maintenance:send_mail_lgpd';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send email with report about lgpd status on plataform';

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
        $message = new Lgpd();
        $emails = [
            'contato@buscaativaescolar.org.br', 
            'dmagalhaes@unicef.org', 
            'rcorreia@unicef.org', 
            'mansouza@unicef.org', 
            'vbezerra@unicef.org', 
            'maraujo@unicef.org', 
            'ndasilva@unicef.org', 
            'adamas@unicef.org', 
            'marangel@unicef.org'];
        Mail::to($emails)->send($message);
    }
}
