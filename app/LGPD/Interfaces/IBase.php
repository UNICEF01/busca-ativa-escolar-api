<?php

namespace BuscaAtivaEscolar\LGPD\Interfaces;

use Illuminate\Database\Eloquent\Model;

interface IBase
{
  public function save(array $attributes): Model;

  public function find(string $id): ?Model;

  public function update(array $attributes, string $id): bool;

  public function delete(string $id): bool;
}
