<?php

namespace App\Services;

use App\Dtos\Transaction\DepositData;
use App\Dtos\Transaction\TransferData;
use App\Enums\TransactionStatus;
use App\Enums\TransactionType;
use App\Interfaces\TransactionServiceInterface;
use App\Models\Account;
use App\Models\Transaction;
use Carbon\Exceptions\InvalidTypeException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TransactionService implements TransactionServiceInterface
{
    public function __construct()
    {
    }

    protected function model()
    {
        return new Transaction();
    }

    public function createTransfer(TransferData $data): Transaction
    {
        return DB::transaction(function () use ($data) {
            $user = Auth::user();

            $originAccount = $user->accounts()
                ->lockForUpdate()
                ->findOrFail($data->origin_account_id);

            $originAccount->hasBalance($data->amount);

            $destinationAccount = Account::lockForUpdate()->findOrFail($data->destination_account_id);

            $originAccount->decreaseBalance($data->amount);
            $destinationAccount->increaseBalance($data->amount);

            $transaction = $this->model()
                ->create([
                    'type'                   => TransactionType::TRANSFER->value,
                    'status'                 => TransactionStatus::FINISHED->value,
                    'amount'                 => $data->amount,
                    'origin_account_id'      => $originAccount->id,
                    'destination_account_id' => $destinationAccount->id,
                    'user_id'                => $user->id,
                ]);

            $transaction->createExtract('Transfer successfully created.', $user->id);

            return $transaction;
        });
    }

    public function createDeposit(DepositData $data): Transaction
    {
        return DB::transaction(function () use ($data) {
            $user = Auth::user();

            $account = $user->accounts()
                ->lockForUpdate()
                ->findOrFail($data->destination_account_id);

            $account->increaseBalance($data->amount);

            $transaction = $this->model()
                ->create([
                    'type'                   => TransactionType::DEPOSIT->value,
                    'status'                 => TransactionStatus::FINISHED->value,
                    'amount'                 => $data->amount,
                    'destination_account_id' => $account->id,
                    'user_id'                => $user->id,
                ]);

            $transaction->createExtract('Deposit successfully created.', $user->id);

            return $transaction;
        });
    }

    public function cancelTransaction($data): Transaction
    {
        return DB::transaction(function () use ($data) {
            $userId = Auth::id();

            $transaction = $this->model()
                ->where(['user_id' => $userId])
                ->lockForUpdate()
                ->findOrFail($data->transaction_id);

            if ($transaction->status != TransactionStatus::CHARGEBACK->value) {
                return match ($transaction->type) {
                    TransactionType::DEPOSIT->value => $this->cancelDeposit($transaction, $userId),
                    TransactionType::TRANSFER->value => $this->cancelTransfer($transaction, $userId),
                    default => throw new InvalidTypeException('This transaction is not refundable.')
                };
            } else {
                throw new InvalidTypeException('Transaction has already been refunded.');
            }
        });
    }

    public function getTransactions(): Collection
    {
        $user = Auth::user();

        return $user->transactions()->get();
    }

    private function cancelDeposit($transaction, $userId): Transaction
    {
        $account = $this->getAccount($transaction->destination_account_id);
        $account->decreaseBalance($transaction->amount);

        $transaction->status = TransactionStatus::CHARGEBACK->value;
        $transaction->save();

        $transaction->createExtract('Deposit successfully returned.', $userId);

        return $transaction;
    }

    private function cancelTransfer($transaction, $userId): Transaction
    {
        $originAccount      = $this->getAccount($transaction->origin_account_id);
        $destinationAccount = $this->getAccount($transaction->destination_account_id);

        $originAccount->increaseBalance($transaction->amount);
        $destinationAccount->decreaseBalance($transaction->amount);

        $transaction->status = TransactionStatus::CHARGEBACK->value;
        $transaction->save();

        $transaction->createExtract('Transfer successfully returned.', $userId);

        return $transaction;
    }

    private function getAccount(int $id): Account
    {
        return Account::lockForUpdate()->findOrFail($id);
    }
}
