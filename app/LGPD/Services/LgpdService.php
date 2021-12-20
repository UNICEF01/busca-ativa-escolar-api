<?php

namespace BuscaAtivaEscolar\LGPD\Services;

use BuscaAtivaEscolar\LGPD\Interfaces\ILgpd;
use BuscaAtivaEscolar\LGPD\Repository\LgpdRepository;

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

  public function checkAccess(string $mail): bool
  {
    $user = User::where('email', $mail)->first();
    if($user && $user->lgpd === 1 && $user->type  !== 'gestor_nacional'){
      if ($this->findLgpd($user->id)) return true;
    }
    return false;
  }
}
