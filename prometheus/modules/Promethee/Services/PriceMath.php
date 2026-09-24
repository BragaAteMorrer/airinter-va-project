<?php
namespace Modules\Promethee\Services;
use InvalidArgumentException;
class PriceMath
{
    public static function adjust(float $price, string $mode, float $value, int $precision = 2): float
    {
        $result = match ($mode) {
            'percent' => $price * (1 + $value / 100),
            'add' => $price + $value,
            'set' => $value,
            default => throw new InvalidArgumentException('Opération inconnue.'),
        };
        if (!is_finite($result) || $result < 0 || $result > 999999.99) {
            throw new InvalidArgumentException('Le prix doit rester compris entre 0 et 999 999,99.');
        }
        return round($result, $precision);
    }
}
