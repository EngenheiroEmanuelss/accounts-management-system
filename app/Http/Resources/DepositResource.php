<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class DepositResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id'      => $this->id,
            'type'    => $this->type,
            'status'  => $this->status,
            'amount'  => $this->amount,
            'account' => $this->destination_account_id,
            'user_id' => $this->user_id,
        ];
    }
}
