<?php

namespace App\Enums;


use App\Enums\Traits\EnumToArray;

enum TransactionStatus: string
{
    use EnumToArray;

    case PENDING = 'pending';
    case FINISHED = 'finished';
    case CHARGEBACK = 'chargeback';
}
