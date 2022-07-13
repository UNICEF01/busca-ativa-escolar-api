<?php


namespace BuscaAtivaEscolar;


use Carbon\Carbon;
use Excel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Log;
use File;
use Rap2hpoutre\FastExcel\FastExcel;

class ExportUsersJob implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    public function handle()
    {

        Log::info("Iniciando processo de exportacao de usuários");

        set_time_limit(0);


        function usersGenerator()
        {
            foreach (User::withTrashed()->get() as $user) {
                yield $user;
            }
        }

        // Export consumes only a few MB, even with 10M+ rows.
        $users = usersGenerator();
        File::makeDirectory(storage_path("app/attachments/user_reports/"), $mode = 0777, true, true);
        (new FastExcel($users))->export(storage_path('app/attachments/user_reports/buscaativaescolar_users_' . Carbon::now()->timestamp . '.xlsx'), function ($userDate) {
            return [

                'Nome do usuário' => $userDate->name,
                'E-mail' => $userDate->email,
                'UF' => $userDate->uf,
                'Município' => $userDate->work_city_name,
                'Instituição' => $userDate->institution,
                'Telefone Institucional' => $userDate->work_phone,
                'Posição' => $userDate->position,
                'Tipo' => trans('user.type.' . $userDate->type),
                'Cadastro' => $userDate->deleted_at ? 'Desativado' : 'Ativo',
            ];
        });


        Log::info("Finalizando processo de exportacao de usuários");
    }
}
