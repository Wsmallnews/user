<?php

namespace Wsmallnews\User\Enums;

use BackedEnum;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;
use Filament\Support\Icons\Heroicon;
use Wsmallnews\Support\Enums\Traits\EnumHelper;

enum Gender: string implements HasColor, HasIcon, HasLabel
{
    use EnumHelper;

    case Male = 'male';

    case Female = 'female';

    case Undisclosed = 'undisclosed';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::Undisclosed => '保密',
            self::Male => '先生',
            self::Female => '女士',
        };
    }

    public function getColor(): string | array | null
    {
        return match ($this) {
            self::Undisclosed => 'gray',
            self::Male => 'success',
            self::Female => 'gray',
        };
    }

    public function getIcon(): string | BackedEnum | null
    {
        return match ($this) {
            self::Undisclosed => Heroicon::OutlinedEyeSlash,
            self::Male => 'eos-male',
            self::Female => 'eos-female',
        };
    }
}
