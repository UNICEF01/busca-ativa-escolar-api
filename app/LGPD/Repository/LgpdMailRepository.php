<?php

namespace BuscaAtivaEscolar\LGPD\Repository;

use Carbon\Carbon;
use BuscaAtivaEscolar\LgpdMail;
use Illuminate\Database\Eloquent\Model;

class LgpdMailRepository extends BaseRepository
{
  protected $lgpdMail;

  public function __construct(LgpdMail $lgpdMail)
  {
    parent::__construct($lgpdMail);
  }

  public function save(array $attributes): Model
  {
    $dataLgpd = new $this->model;
    $dataLgpd->plataform_id = $attributes['plataform_id'];
    $dataLgpd->mail = $attributes['mail'];
    $dataLgpd->send_date = $attributes['send_date'];
    $dataLgpd->delivery_date = null;
    $dataLgpd->open_date = null;
    $dataLgpd->click_date = null;
    $dataLgpd->save();
    return $dataLgpd->fresh();
  }

  public function update(array $attributes, string $id): bool
  {

    $dataLgpd = $this->model->where('plataform_id', $id)->first();
    $dataLgpd->mail = $attributes['mail'];
    $dataLgpd->send_date = $attributes['send_date'];
    $dataLgpd->delivery_date = $attributes['delivery_date'];
    $dataLgpd->open_date = $attributes['open_date'];
    $dataLgpd->click_date = $attributes['click_date'];
    return $dataLgpd->update();
  }
}
