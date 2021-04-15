<?php

namespace App\Observers;

use App\Account;
use App\CalculateAccountBalance;
use App\Transaction;

class TransactionObserver
{
    public function __construct(CalculateAccountBalance $calculator)
    {
        $this->calculator = $calculator;
    }

    public function created(Transaction $transaction)
    {
        return $this->calculator->calculateNewAccountBalance($transaction);
    }

    public function updating(Transaction $transaction)
    {

        $this->calculator->setOldTransaction(Transaction::find($transaction->id));
    }

    public function updated(Transaction $transaction)
    {
        $this->calculator->setAccountBalanceFromOldTransaction($transaction);

        return $this->calculator->calculateNewAccountBalance($transaction);
    }

    public function deleted(Transaction $transaction)
    {
        $this->calculator->setOldTransaction($transaction);
        $account = $this->calculator->setAccountBalanceFromOldTransaction($transaction);
        $account->save();
    }
}
