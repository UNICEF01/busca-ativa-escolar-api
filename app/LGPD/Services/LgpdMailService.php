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


    for ($i = 0; $i < count($result); $i++) {
      if ($result[$i]->getEvent() === 'delivered')
        $delivered = array($result[$i]);
      if ($result[$i]->getEvent() === 'opened')
        $opened = array($result[$i]);
      if ($result[$i]->getEvent() === 'clicked')
        $clicked = array($result[$i]);
    }


    $attributes = [
      'mail' => $mail,
      'delivery_date' => (isset($delivered) && !empty($delivered)) ? Carbon::createFromTimestamp($delivered[0]->getTimestamp())->toDateTimeString() : null,
      'open_date' => (isset($opened) && !empty($opened))  ? Carbon::createFromTimestamp($opened[0]->getTimestamp())->toDateTimeString() : null,
      'click_date' => (isset($clicked) && !empty($clicked))  ? Carbon::createFromTimestamp($clicked[0]->getTimestamp())->toDateTimeString() : null,
    ];


    return $this->lgpdMailRepository->update($attributes, $id);
  }

  public function getMail($mail, $id)
  {
    $mgClient = Mailgun::create(env('MAILGUN_API_KEY'));
    $domain = env('MAILGUN_API_DOMAIN');

    $queryString = array(
      'begin' => 'Thu, 30 Sep 2021 11:30:00 -0300',
      'ascending' => 'yes',
      'limit' => 100,
      'pretty' => 'yes',
      'recipient' => $mail,
      'message-id' => $id
    );
    $result = $mgClient->events()->get($domain, $queryString)->getItems();
    return $result;
  }
}
