<?php

namespace App\Dtos\Transaction;

use Spatie\LaravelData\Attributes\Validation\Min;
use Spatie\LaravelData\Data;

class DepositData extends Data
{
    #[Min(1)]
    public int $amount;
    #[Min(0)]
    public int $destination_account_id;
}
