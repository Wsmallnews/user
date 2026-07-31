<?php

namespace Wsmallnews\User\Enums;

use BackedEnum;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\Support\Htmlable;
use Wsmallnews\Support\Enums\Traits\EnumHelper;

enum Status: string implements HasColor, HasIcon, HasLabel
{
    use EnumHelper;

    case Normal = 'normal';

    case Disabled = 'disabled';

    public function getLabel(): string | Htmlable | null
    {
        return match ($this) {
            self::Normal => __('sn-user::user.status.normal'),
            self::Disabled => __('sn-user::user.status.disabled'),
        };
    }

    public function getColor(): string | array | null
    {
        return match ($this) {
            self::Normal => 'success',
            self::Disabled => 'danger',
        };
    }

    public function getIcon(): string | BackedEnum | Htmlable | null
    {
        return match ($this) {
            self::Normal => Heroicon::OutlinedCheckCircle,
            self::Disabled => Heroicon::OutlinedNoSymbol,
        };
    }
}
