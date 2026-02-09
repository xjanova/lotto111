<?php

namespace App\Services;

use App\Enums\TransactionType;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class BalanceService
{
    /**
     * Credit user balance
     */
    public function credit(
        User $user,
        float $amount,
        string $description,
        TransactionType $type,
        ?int $referenceId = null,
        ?string $referenceType = null,
    ): array {
        return DB::transaction(function () use ($user, $amount, $description, $type, $referenceId, $referenceType) {
            $balanceBefore = (float) $user->balance;
            $user->increment('balance', $amount);
            $user->refresh();
            $balanceAfter = (float) $user->balance;

            $transaction = Transaction::create([
                'user_id' => $user->id,
                'type' => $type,
                'amount' => $amount,
                'balance_before' => $balanceBefore,
                'balance_after' => $balanceAfter,
                'reference_type' => $referenceType,
                'reference_id' => $referenceId,
                'description' => $description,
            ]);

            return [
                'success' => true,
                'transaction_id' => $transaction->id,
                'new_balance' => $balanceAfter,
            ];
        });
    }

    /**
     * Debit user balance
     */
    public function debit(
        User $user,
        float $amount,
        string $description,
        TransactionType $type,
        ?int $referenceId = null,
        ?string $referenceType = null,
    ): array {
        if ((float) $user->balance < $amount) {
            throw new \RuntimeException('Insufficient balance');
        }

        return DB::transaction(function () use ($user, $amount, $description, $type, $referenceId, $referenceType) {
            $balanceBefore = (float) $user->balance;
            $user->decrement('balance', $amount);
            $user->refresh();
            $balanceAfter = (float) $user->balance;

            $transaction = Transaction::create([
                'user_id' => $user->id,
                'type' => $type,
                'amount' => -$amount,
                'balance_before' => $balanceBefore,
                'balance_after' => $balanceAfter,
                'reference_type' => $referenceType,
                'reference_id' => $referenceId,
                'description' => $description,
            ]);

            return [
                'success' => true,
                'transaction_id' => $transaction->id,
                'new_balance' => $balanceAfter,
            ];
        });
    }

    /**
     * Get balance for user
     */
    public function getBalance(User $user): float
    {
        return (float) $user->fresh()->balance;
    }

    /**
     * Get transaction history
     */
    public function getTransactions(User $user, ?string $type = null, int $limit = 20): mixed
    {
        $query = Transaction::where('user_id', $user->id)
            ->orderBy('created_at', 'desc');

        if ($type) {
            $query->where('type', $type);
        }

        return $query->paginate($limit);
    }
}
