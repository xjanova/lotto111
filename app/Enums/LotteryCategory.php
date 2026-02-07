<?php

namespace App\Enums;

enum LotteryCategory: string
{
    case Government = 'government';
    case Yeekee = 'yeekee';
    case Bank = 'bank';
    case International = 'international';
    case Set = 'set';

    public function label(): string
    {
        return match ($this) {
            self::Government => 'หวยรัฐบาล',
            self::Yeekee => 'จับยี่กี',
            self::Bank => 'หวยธนาคาร',
            self::International => 'หวยต่างประเทศ',
            self::Set => 'หวยชุด',
        };
    }
}
