<?php

namespace Wsmallnews\User\Enums;

use BackedEnum;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\Support\Htmlable;
use Wsmallnews\Support\Enums\Traits\EnumHelper;

enum Gender: string implements HasColor, HasIcon, HasLabel
{
    use EnumHelper;

    case Male = 'male';

    case Female = 'female';

    case Undisclosed = 'undisclosed';

    public function getLabel(): string | Htmlable | null
    {
        return match ($this) {
            self::Undisclosed => __('sn-user::user.gender.undisclosed'),
            self::Male => __('sn-user::user.gender.male'),
            self::Female => __('sn-user::user.gender.female'),
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

    public function getIcon(): string | BackedEnum | Htmlable | null
    {
        return match ($this) {
            self::Undisclosed => Heroicon::OutlinedEyeSlash,
            self::Male => 'eos-male',
            self::Female => 'eos-female',
        };
    }
}
