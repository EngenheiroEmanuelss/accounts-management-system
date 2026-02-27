<?php

namespace App\Enums;


use App\Enums\Traits\EnumToArray;

enum TransactionType: string
{
    use EnumToArray;

    case TRANSFER = 'transfer';
    case DEPOSIT = 'deposit';
}
