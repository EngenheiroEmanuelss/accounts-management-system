<?php

namespace App\Interfaces;

use App\Dtos\Transaction\DepositData;
use App\Dtos\Transaction\ReversalData;
use App\Dtos\Transaction\TransferData;
use App\Models\Transaction;
use Illuminate\Support\Collection;

interface TransactionServiceInterface
{
    public function createTransfer(TransferData $data): Transaction;

    public function createDeposit(DepositData $data): Transaction;

    public function cancelTransaction(ReversalData $data): Transaction;
    public function getTransactions(): Collection;
}
