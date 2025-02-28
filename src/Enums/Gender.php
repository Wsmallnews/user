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
            self::Male => '男',
            self::Female => '女',
            self::Other => '其他',
        };
    }

    public function getColor(): string | array | null
    {
        return match ($this) {
            self::Male => 'success',
            self::Female => 'gray',
            self::Other => 'gray',
        };
    }
}
