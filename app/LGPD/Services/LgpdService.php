<?php

namespace BuscaAtivaEscolar\LGPD\Services;

use BuscaAtivaEscolar\LGPD\Interfaces\ILgpd;
use BuscaAtivaEscolar\LGPD\Repository\LgpdRepository;
use BuscaAtivaEscolar\User;
use BuscaAtivaEscolar\Tenant;
use BuscaAtivaEscolar\TenantSignup;

class LgpdService implements ILgpd
{
  protected $lgpdRepository;

  public function __construct(LgpdRepository $lgpdRepository)
  {
    $this->lgpdRepository = $lgpdRepository;
  }


  public function findLgpd(string $id): ?object
  {
    return $this->lgpdRepository->find($id);
  }

  public function saveLgpd(array $attributes): object
  {
    return $this->lgpdRepository->save($attributes);
  }

  public function updateLgpd(array $attributes, string $id): bool
  {
    return $this->lgpdRepository->update($attributes, $id);
  }

  public function deleteLgpd(string $id): bool
  {
    //TODO
    return 1;
  }

  public function checkAccess(array $attributes): bool
  {
    if ($this->findLgpd($attributes['user'])) {
      /*if (array_key_exists('uf', $attributes) && !is_null($attributes['uf'])) {
      if ($this->findLgpd($attributes['uf']))
      return true;
      return false;
      }
      if (array_key_exists('tenant_id', $attributes) && !is_null($attributes['tenant_id'])) {
      if ($this->findLgpd($attributes['tenant_id']))
      return true;
      return false;
      }*/
      return true;
    }

    return false;
  }
}