<?php

namespace App\Dtos\Transaction;

use Spatie\LaravelData\Attributes\Validation\Min;
use Spatie\LaravelData\Data;

class ReversalData extends Data
{
    #[Min(0)]
    public int $transaction_id;
}
