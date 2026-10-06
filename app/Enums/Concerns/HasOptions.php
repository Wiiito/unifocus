<?php

namespace App\Enums\Concerns;

/**
 * Expõe os casos de um enum com rótulo como pares valor => rótulo, prontos
 * para popular <select>s e validações sem repetir a lista em cada tela.
 */
trait HasOptions
{
    /**
     * @return array<string, string>
     */
    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $case): array => [$case->value => $case->label()])
            ->all();
    }

    /**
     * @return array<int, string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
