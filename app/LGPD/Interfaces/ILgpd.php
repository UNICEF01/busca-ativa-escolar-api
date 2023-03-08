<?php

namespace BuscaAtivaEscolar\LGPD\Interfaces;

interface ILgpd
{
  public function findLgpd(string $id): ?object;
  public function saveLgpd(array $attributes): object;
  public function updateLgpd(array $attributes, string $id): bool;
  public function deleteLgpd(string $id): bool;
  public function checkAccess(array $attributes): bool;
}