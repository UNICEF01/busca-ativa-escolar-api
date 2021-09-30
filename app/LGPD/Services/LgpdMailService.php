<?php

namespace BuscaAtivaEscolar\LGPD\Services;

use BuscaAtivaEscolar\LGPD\Interfaces\IMail;
use BuscaAtivaEscolar\LGPD\Repository\LgpdMailRepository;
use Mailgun\Mailgun as MailGun;
use Illuminate\Support\Carbon;

class LgpdMailService implements IMail
{

  protected $lgpdMailRepository;

  public function __construct(LgpdMailRepository $lgpdMailRepository)
  {
    $this->lgpdMailRepository = $lgpdMailRepository;
  }

  public function saveMail(array $attributes)
  {
    $attributes['send_date'] = Carbon::now();
    return $this->lgpdMailRepository->save($attributes);
  }

  public function updateMail(string $id, string $mail)
  {

    $result = $this->getMail($mail, $id);
    $attributes = [
      'mail' => $mail,
      'send_date' => Carbon::createFromTimestamp($result[0]->getTimestamp())->toDateTimeString(),
      'delivery_date' => Carbon::createFromTimestamp($result[1]->getTimestamp())->toDateTimeString(),
      'open_date' => Carbon::createFromTimestamp($result[2]->getTimestamp())->toDateTimeString(),
      'click_date' => Carbon::createFromTimestamp($result[3]->getTimestamp())->toDateTimeString(),
    ];


    return $this->lgpdMailRepository->update($attributes, $id);
  }

  public function getMail($mail, $id)
  {
    $mgClient = Mailgun::create(env('MAILGUN_API_KEY'));
    $domain = env('MAILGUN_API_DOMAIN');

    $queryString = array(
      'begin' => 'Wed, 29 Sep 2021 12:30:00 -0300',
      'ascending' => 'yes',
      'limit' => 100,
      'pretty' => 'yes',
      'recipient' => $mail,
      'message-id' => $id
    );
    $result = $mgClient->events()->get($domain, $queryString)->getItems();
    //dump($result);
    return $result;
  }
}
