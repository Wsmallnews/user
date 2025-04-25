<?php

namespace Wsmallnews\User\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;
use Wsmallnews\Support\Enums\Traits\EnumHelper;

enum Gender: string implements HasColor, HasLabel
{
    use EnumHelper;

    case Male = 'male';

    case Female = 'female';

    case Other = 'other';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::Other => '保密',
            self::Male => '先生',
            self::Female => '女士',
        };
    }

    public function getColor(): string | array | null
    {
        return match ($this) {
            self::Other => 'gray',
            self::Male => 'success',
            self::Female => 'gray',
        };
    }
}
