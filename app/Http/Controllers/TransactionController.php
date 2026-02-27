<?php

namespace App\Http\Controllers;

use App\Dtos\Transaction\DepositData;
use App\Dtos\Transaction\ReversalData;
use App\Dtos\Transaction\TransferData;
use App\Http\Resources\DepositResource;
use App\Http\Resources\ReversalResource;
use App\Http\Resources\TransferResource;
use App\Http\Resources\TransferResourceCollection;
use App\Interfaces\TransactionServiceInterface;

class TransactionController extends Controller
{
    private TransactionServiceInterface $transactionService;

    public function __construct(TransactionServiceInterface $transactionService)
    {
        $this->transactionService = $transactionService;
    }

    public function transfer(TransferData $data)
    {
        return response()->json(new TransferResource($this->transactionService->createTransfer($data)), 201);
    }

    public function deposit(DepositData $data)
    {
        return response()->json(new DepositResource($this->transactionService->createDeposit($data)), 201);
    }

    public function reversal(ReversalData $data)
    {
        return response()->json(
            new ReversalResource($this->transactionService->cancelTransaction($data)),
            201
        );
    }

    public function getTransactions()
    {
        return response()->json(new TransferResourceCollection($this->transactionService->getTransactions()), 201);
    }
}
