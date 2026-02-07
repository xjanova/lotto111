<?php

namespace App\Enums;

enum DepositMethod: string
{
    case BankTransfer = 'bank_transfer';
    case PromptPay = 'promptpay';
    case TrueWallet = 'truewallet';
    case Auto = 'auto';

    public function label(): string
    {
        return match ($this) {
            self::BankTransfer => 'โอนผ่านธนาคาร',
            self::PromptPay => 'PromptPay',
            self::TrueWallet => 'TrueWallet',
            self::Auto => 'อัตโนมัติ',
        };
    }
}
