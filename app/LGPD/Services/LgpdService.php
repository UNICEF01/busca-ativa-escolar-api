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

  public function checkAccess(string $mail): bool
  {
    $user = User::where('email', $mail)->first();
    if($user && $user->lgpd === 1){
      if(str_contains($user->type, 'estadual')){
        return $this->findLgpd($user->id) && $this->findLgpd($user->uf) ? true : false;
      }
      else{
        if(str_contains($user->type, 'nacional')){
          return $this->findLgpd($user->id) ? true : false;
        }
        else{
          $tenantData = Tenant::where('id', $user->tenant_id)->first();
          $signupTenantData = DB::table("tenant_signups ts")
          ->select("id")
          ->whereNull("deleted_at")
          ->where(DB::raw("(tenant_id = (select id from tenants t where id = $tenantData) or 
                            city_id = (select city_id  from tenants t  where id = $tenantData)) "))
          ->get();
          return $this->findLgpd($user->id) && $this->findLgpd($signupTenantData) ? true : false;
        }
      }
    }
    return false;
  }
}
