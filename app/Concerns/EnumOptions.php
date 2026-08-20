<?php

namespace App\Concerns;

/**
 * Единый формат опций для селектов админки (Inertia -> Vue).
 */
trait EnumOptions
{
    /** @return array<array{value: string, label: string}> */
    public static function options(): array
    {
        return array_map(
            fn (self $case) => [
                'value' => $case->value,
                'label' => method_exists($case, 'label') ? $case->label() : $case->name,
            ],
            self::cases()
        );
    }
}
