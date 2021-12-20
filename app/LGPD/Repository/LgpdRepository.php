<?php

namespace BuscaAtivaEscolar\LGPD\Repository;

use Carbon\Carbon;
use BuscaAtivaEscolar\Lgpd;
use Illuminate\Database\Eloquent\Model;

class LgpdRepository extends BaseRepository
{
  protected $lgpd;

  public function __construct(Lgpd $lgpd)
  {
    parent::__construct($lgpd);
  }

  public function save(array $attributes): Model
  {
    $dataLgpd = new $this->model;
    $dataLgpd->plataform_id = $attributes['plataform_id'];
    $dataLgpd->name = $attributes['name'];
    $dataLgpd->ip_addr = $attributes['ip_addr'];
    $dataLgpd->assigned_date = Carbon::now()->toDateTimeString();
    $dataLgpd->term_version = 1;
    $dataLgpd->save();
    return $dataLgpd->fresh();
  }

  public function find(string $id): ?Model
  {
    $ufs = [
      'RO','AC','AM','RR','PA',
      'AP','TO','MA','PI','CE',
      'RN','PB','PE','AL','SE',
      'BA','MG','ES','RJ','SP',
      'PR','SC','RS','MS','MT',
      'GO','DF'
    ];
    
    if (in_array($id, $ufs)) {
      return $this->model->where('name', $id)->first();
    }
    return $this->model->where('plataform_id', $id)->first();
  }

  public function update(array $attributes, string $id): bool
  {

    $dataLgpd = $this->model->where('plataform_id', $id)->first();
    $dataLgpd->plataform_id = $attributes['plataform_id'];
    return $dataLgpd->update();
  }
}
