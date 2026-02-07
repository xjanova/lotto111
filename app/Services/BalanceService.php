<?php

namespace App\Services;

use App\Enums\TransactionType;
use App\Models\User;

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
        $user->increment('balance', $amount);

        return [
            'success' => true,
            'transaction_id' => null, // will be set when Transaction model exists
            'new_balance' => $user->balance,
        ];
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
        if ($user->balance < $amount) {
            throw new \RuntimeException('Insufficient balance');
        }

        $user->decrement('balance', $amount);

        return [
            'success' => true,
            'transaction_id' => null,
            'new_balance' => $user->balance,
        ];
    }
}
