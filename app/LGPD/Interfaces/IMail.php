<?php

namespace BuscaAtivaEscolar\LGPD\Interfaces;

interface IMail
{
  public function saveMail(array $attributes);
  public function updateMail(string $id, string $mail);
  public function getMail(string $mail, string $id);
}
