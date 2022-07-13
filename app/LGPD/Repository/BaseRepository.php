<?php

namespace BuscaAtivaEscolar\LGPD\Repository;

use BuscaAtivaEscolar\LGPD\Interfaces\IBase;
use Illuminate\Database\Eloquent\Model;

class BaseRepository implements IBase
{
  protected $model;
  public function __construct(Model $model)
  {
    $this->model = $model;
  }

  /**
   * Function save element on Database
   * @param array $attributes
   * @return Model
   */
  public function save(array $attributes): Model
  {
    return $this->model->newQuery()->create($attributes);
  }

  /**
   * Function return element from Database
   * @param $id
   * @return Model
   */
  public function find(string $id): ?Model
  {
    return $this->model->find($id);
  }


  /**
   * Function update element from Database
   * @param $id
   * @return bool
   */

  public function update(array $attributes,  string $id): bool
  {
    return $this->model->update($attributes);
  }

  /**
   * Function delete element from Database
   * @return bool
   */
  public function delete(string $id): bool
  {
    return $this->model->delete();
  }
}
