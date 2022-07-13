<?php
/**
 * Created by PhpStorm.
 * User: manoelfilho
 * Date: 2019-03-10
 * Time: 11:53
 */

namespace BuscaAtivaEscolar;


use BuscaAtivaEscolar\EmailTypes\SchoolFrequencyEmail;
use BuscaAtivaEscolar\EmailTypes\SendEmail;
use BuscaAtivaEscolar\EmailTypes\SchoolEducacensoEmail;
use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;


class EmailJob extends Model
{

    protected $table = "email_jobs";

    const PUBLIC_FIELDS = [
        'id',
        'created_at',
        'type',
        'status',
        'user_id',
        'tenant_id',
        'school_id',
        'school_email'
    ];

    protected $fillable = [
        'type',
        'status',
        'user_id',
        'tenant_id',
        'school_id',
        'errors',
        'school_email',
        'email_user'
    ];

    protected $casts = [
        'errors' => 'array'
    ];

    const TYPES = [
        'school_educacenso_email' => SchoolEducacensoEmail::class,
        'school_frequency_email' => SchoolFrequencyEmail::class
    ];

    const STATUS_PENDING = "pending";
    const STATUS_ACCEPTED = "accepted";
    const STATUS_REJECTED = "rejected";
    const STATUS_DELIVERED = "delivered";
    const STATUS_FAILED = "failed";
    const STATUS_OPENED = "opened";
    const STATUS_CLICKED = "clicked";
    const STATUS_UNSUBSCRIBED = "unsubscribed";
    const STATUS_COMPLAINED = "complained";
    const STATUS_STORED = "stored";

    /**
     * @param string $emailType
     * @param User $user
     * @param School $school
     * @return Model|mixed
     * @throws \Exception
     */
    public static function createFromType(string $emailType, User $user, $school){

        if(!in_array($emailType, array_keys(self::TYPES))) {
            throw new \Exception("Invalid email type: {$emailType}");
        }

        return self::create([
            'type' => $emailType,
            'status' => self::STATUS_PENDING,
            'user_id' => $user->id,
            'tenant_id' => $user->tenant ? $user->tenant->id : null,
            'school_id' => $school->id,
            'school_email' => $school->school_email,
            'email_user' => $user->email
        ]);

    }

    public function setStatus($status) {
        $this->update(['status' => $status]);
    }

    public function handle() {
        set_time_limit(0);
        ignore_user_abort(true);
        $class = self::TYPES[$this->type];
        if(!$class) throw new InvalidArgumentException("No handler for email type: {$this->type}");
        $handler = new $class(); /* @var $handler SendEmail */
        return $handler->handle($this);
    }

    public function saveError(\Exception $ex) {
        if(!$this->errors) $this->errors = [];
        $errors = $this->errors;
        array_push($errors, $ex->getMessage());
        $this->errors = $errors;
    }


}