<?php

namespace BuscaAtivaEscolar\Console\Commands;

use BuscaAtivaEscolar\Classe;
use BuscaAtivaEscolar\EmailTypes\ClassFrequencyNotification;
use BuscaAtivaEscolar\Frequency;
use BuscaAtivaEscolar\School;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SendEmailsActualizeFrequency extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'maintenance:send_emails_actualize_frequency';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send a email to all schools that have classes with controll of frequency';

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
     * @return mixed
     */
    public function handle()
    {
        set_time_limit(0);
        //ini_set('memory_limit', '2G');

        //$this->comment("INICIANDO PROCESSO DE ENVIO DE EMAILS PARA AS ESCOLAS COM BASE NAS ESCOLAS");
        School::has('classes')->chunk(50, function($schools){

            foreach ($schools as $school){

                $today = intval(date('d')); //number of day in the month
                $today_week = intval(date('w')); //number of day in the week (0 - 6)
                $dayOfMidleOfMonth = intval(date("t")/2); //number

                //DIARIA SE PERIODICIDADE DIARIA E DIA DA SEMANA ATE SEXTA_FEIRA
                if( $school->periodicidade === School::PERIODICIDADE_DIARIA AND ($today_week > 0 AND $today_week <= 5) ) {
                    $this->createFrequenciesBySchool($school);
                    try {
                        $message = new ClassFrequencyNotification($school, School::PERIODICIDADE_DIARIA);
                        Mail::to($school->school_email)->send($message);
                        //$this->info("MENSAGEM ENCAMINHADA COM SUCESSO - INEP: ". $school->id. " | ". $school->name);
                    } catch (\Exception $ex) {
                        //$this->info($ex->getMessage());
                        //$this->info("ERRO NO ENVIO DE MENSAGEM INEP: ". $school->id. " | ". $school->name);
                    }
                }

                //SEMANAL SE PERIODICIDADE SEMANAL E DIA DA SEMANA SEGUNDA
                if( $school->periodicidade === School::PERIODICIDADE_SEMANAL AND ( $today_week === 1) ) {
                    $this->createFrequenciesBySchool($school);
                    try {
                        $message = new ClassFrequencyNotification($school, School::PERIODICIDADE_SEMANAL);
                        Mail::to($school->school_email)->send($message);
                        //$this->info("MENSAGEM ENCAMINHADA COM SUCESSO - INEP: ". $school->id. " | ". $school->name);
                    } catch (\Exception $ex) {
                        //$this->info($ex->getMessage());
                        //$this->info("ERRO NO ENVIO DE MENSAGEM INEP: ". $school->id. " | ". $school->name);
                    }
                }

                //QUINZENAL SE PERIODICIDADE QUINZENAL E DIA DO MES É METADE DO MES MAIS 1 OU 1 DO MES
                if( $school->periodicidade === School::PERIODICIDADE_QUINZENAL AND ( $today === ($dayOfMidleOfMonth + 1) OR $today === 1 ) ) {
                    $this->createFrequenciesBySchool($school);
                    try {
                        $message = new ClassFrequencyNotification($school, School::PERIODICIDADE_QUINZENAL);
                        Mail::to($school->school_email)->send($message);
                        //$this->info("MENSAGEM ENCAMINHADA COM SUCESSO - INEP: ". $school->id. " | ". $school->name);
                    } catch (\Exception $ex) {
                        //$this->info($ex->getMessage());
                        //$this->info("ERRO NO ENVIO DE MENSAGEM INEP: ". $school->id. " | ". $school->name);
                    }
                }

                //MENSAL SE PERIODICIDADE MENSAL E DIA E 1 DO MES
                if( $school->periodicidade === School::PERIODICIDADE_MENSAL AND ( $today === 1 ) ) {
                    $this->createFrequenciesBySchool($school);
                    try {
                        $message = new ClassFrequencyNotification($school, School::PERIODICIDADE_MENSAL);
                        Mail::to($school->school_email)->send($message);
                        //$this->info("MENSAGEM ENCAMINHADA COM SUCESSO - INEP: ". $school->id. " | ". $school->name);
                    } catch (\Exception $ex) {
                        //$this->info($ex->getMessage());
                        //$this->info("ERRO NO ENVIO DE MENSAGEM INEP: ". $school->id. " | ". $school->name);
                    }
                }

            }

        });
        //$this->comment("FINALIZANDO PROCESSO DE ENVIO DE EMAILS PARA AS ESCOLAS COM BASE NAS ESCOLAS");

    }

    //CRIA FREQUENCIAS DEFAULT PARA AS TURMAS DE UMA ESCOLA
    public function createFrequenciesBySchool($school){
        foreach ($school->classes as $classe){

            $today_week = intval(date('w')); //number of day in the week (0 - 6)

            if( $school->periodicidade === School::PERIODICIDADE_DIARIA){

                //SE DE TERÇA A SEXTA-FEIRA
                if( $today_week > 1 AND $today_week <= 5){
                    $savedFrequency = Frequency::where([
                        ['created_at', '>=', Carbon::yesterday()],
                        ['classes_id', '=', $classe->id]
                    ])->first();

                    if ( $savedFrequency == null ){
                        $frequency = new Frequency();
                        $frequency->qty_presence = 0;
                        $frequency->qty_enrollment = 0;
                        $frequency->classes_id = $classe->id;
                        $frequency->created_at =  Carbon::yesterday();
                        $frequency->periodicidade = $school->periodicidade;
                        $frequency->save();
                    }
                }

                //SE SEGUNDA-FEIRA
                if( $today_week == 1 ){
                    $savedFrequency = Frequency::where([
                        ['created_at', '>=', Carbon::now()->subDays(3)],
                        ['classes_id', '=', $classe->id]
                    ])->first();

                    if ( $savedFrequency == null ){
                        $frequency = new Frequency();
                        $frequency->qty_presence = 0;
                        $frequency->qty_enrollment = 0;
                        $frequency->classes_id = $classe->id;
                        $frequency->created_at =  Carbon::now()->subDays(3);
                        $frequency->periodicidade = $school->periodicidade;
                        $frequency->save();
                    }
                }

            }

            if( $school->periodicidade === School::PERIODICIDADE_SEMANAL){
                //SEGUNDA-FEIRA
                if( $today_week == 1 ){
                    $savedFrequency = Frequency::where([
                        ['created_at', '>=', Carbon::now()->subDays(3)],
                        ['classes_id', '=', $classe->id]
                    ])->first();

                    if ( $savedFrequency == null ){
                        $frequency = new Frequency();
                        $frequency->qty_presence = 0;
                        $frequency->qty_enrollment = 0;
                        $frequency->classes_id = $classe->id;
                        $frequency->created_at =  Carbon::now()->subDays(3);
                        $frequency->periodicidade = $school->periodicidade;
                        $frequency->save();
                    }
                }
            }

            if( $school->periodicidade === School::PERIODICIDADE_QUINZENAL){

                $savedFrequency = Frequency::where([
                    ['created_at', '>=', Carbon::yesterday()],
                    ['classes_id', '=', $classe->id]
                ])->first();

                if ( $savedFrequency == null ){
                    $frequency = new Frequency();
                    $frequency->qty_presence = 0;
                    $frequency->qty_enrollment = 0;
                    $frequency->classes_id = $classe->id;
                    $frequency->created_at =  Carbon::yesterday();
                    $frequency->periodicidade = $school->periodicidade;
                    $frequency->save();
                }

            }

            if( $school->periodicidade === School::PERIODICIDADE_MENSAL) {

                $savedFrequency = Frequency::where([
                    ['created_at', '>=', Carbon::yesterday()],
                    ['classes_id', '=', $classe->id]
                ])->first();

                if ( $savedFrequency == null ){
                    $frequency = new Frequency();
                    $frequency->qty_presence = 0;
                    $frequency->qty_enrollment = 0;
                    $frequency->classes_id = $classe->id;
                    $frequency->created_at =  Carbon::yesterday();
                    $frequency->periodicidade = $school->periodicidade;
                    $frequency->save();
                }
            }

        }
    }
}
