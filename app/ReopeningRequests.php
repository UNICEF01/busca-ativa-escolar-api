<?php

namespace BuscaAtivaEscolar;

use BuscaAtivaEscolar\Traits\Data\IndexedByUUID;
use Illuminate\Database\Eloquent\Model;

class ReopeningRequests extends Model
{
    use IndexedByUUID;

    const STATUS_APPROVED = "approved";
    const STATUS_REQUESTED = "requested";
    const STATUS_CANCELLED = "cancelled";

    const TYPE_REQUEST_REOPEN = "reopen";
    const TYPE_REQUEST_TRANSFER = "transfer";

    protected $table = "reopening_requests";

    protected $fillable = [
        'requester_id', //USER REQUESTER
        'recipient_id', //USER RECIPIENT
        'child_id',
        'status',
        'interrupt_reason',
        'reject_reason',

        'tenant_requester_id', //TENANT REQUESTER
        'tenant_recipient_id', //TENANT RECIPIENT

        'type_request'
    ];

    /**
     * The User request of this Reopening Request
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function requester()
    {
        return $this->hasOne('BuscaAtivaEscolar\User', 'id', 'requester_id');
    }

    /**
     * The User recipient that accpeted the request
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function recipient()
    {
        return $this->hasOne('BuscaAtivaEscolar\User', 'id', 'recipient_id');
    }


    /**
     * The Child of the case to be reopened
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function child()
    {
        return $this->hasOne('BuscaAtivaEscolar\Child', 'id', 'child_id');
    }

    /**
     * The Tenant that requesting the transfer
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function tenantRequester()
    {
        return $this->hasOne('BuscaAtivaEscolar\Tenant', 'id', 'tenant_requester_id');
    }

    /**
     * The Tenant that receiving request
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function tenantRecipient()
    {
        return $this->hasOne('BuscaAtivaEscolar\Tenant', 'id', 'tenant_recipient_id');
    }


}