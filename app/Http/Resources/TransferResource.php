<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class TransferResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id'                  => $this->id,
            'type'                => $this->type,
            'status'              => $this->status,
            'amount'              => $this->amount,
            'origin_account'      => $this->origin_account_id,
            'destination_account' => $this->destination_account_id,
            'user_id'             => $this->user_id,
        ];
    }
}
