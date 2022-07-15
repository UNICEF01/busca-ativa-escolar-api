<?php

namespace BuscaAtivaEscolar\NotificationCases\Repositories;


use BuscaAtivaEscolar\NotificationCases;
use BuscaAtivaEscolar\LGPD\Repository\BaseRepository;
use Illuminate\Database\Eloquent\Model;


class NotificationCasesRepository extends BaseRepository
{
  protected $notificationCases;

  public function __construct(NotificationCases $notificationCases)
  {
    parent::__construct($notificationCases);
  }

  public function save(array $attributes): Model
  {  
    $dataNotification = new $this->model;
    $dataNotification->tenant_id = $attributes['tenant_id'];
    $dataNotification->user_id = $attributes['user_id'];
    $dataNotification->comment_id = $attributes['comment_id'];
    $dataNotification->children_case_id = $attributes['children_case_id'];
    $dataNotification->notification = $attributes['notification'];
    $dataNotification->case_tree_id = $attributes['case_tree_id'];
    $dataNotification->users_tree_id = $attributes['users_tree_id'];
    $dataNotification->solved = 0;
    $dataNotification->save();
    return $dataNotification->fresh();
  }

  public function find(string $id): ?Model
  {
    return $this->model->where('id', $id)->first();
  }

  public function getComment(string $id): ?Model
  {
    return $this->model->where('comment_id', $id)->first();
  }

  public function delete(string $id): bool
  {
    return $this->model->find($id)->delete();
  }

  public function findAll(string $tree_id): ?object
  {
    return $this->model->where('users_tree_id', $tree_id)->get();
  }

  public function update(array $attributes, string $id): bool
  {
    return $this->model->find($id)->update('solved', 1);
  }
}