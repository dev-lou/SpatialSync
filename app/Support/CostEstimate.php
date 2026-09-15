<?php

namespace App\Support;

/**
 * Turns stored build parts into a planning cost estimate.
 *
 * Mirrors public/js/cost-estimate.js so the editor can keep the figure fresh as
 * parts are placed while the page still ships an accurate value on first paint.
 * Rates live in config/spatialsync.php.
 *
 * @phpstan-type RateArray array{basis: string, rate: float, label: string}
 * @phpstan-type EstimateLine array{label: string, quantity: float, amount: float, unit: string}
 */
class CostEstimate
{
    /**
     * @param  array<int, mixed>  $parts  rows from build_parts
     * @return array{total: float, formatted: string, currency: string, symbol: string, label: string, note: string, lines: array<int, EstimateLine>}
     */
    public static function forParts(array $parts): array
    {
        /** @var array<string, mixed> $config */
        $config = (array) config('spatialsync.estimates', []);
        $rates = is_array($config['rates'] ?? null) ? $config['rates'] : [];
        $symbol = self::str($config['symbol'] ?? null, '$');

        /** @var array<string, EstimateLine> $lines */
        $lines = [];
        $total = 0.0;

        foreach ($parts as $part) {
            $row = (array) $part;
            $type = self::str($row['type'] ?? null, 'generic');
            $rate = self::rateFor($type, $rates);

            $quantity = self::quantityFor($row, $rate['basis']);
            $amount = $quantity * $rate['rate'];

            $total += $amount;

            $label = $rate['label'];

            if (! isset($lines[$label])) {
                $lines[$label] = [
                    'label' => $label,
                    'quantity' => 0.0,
                    'amount' => 0.0,
                    'unit' => $rate['basis'] === 'area' ? 'm²' : 'units',
                ];
            }

            $lines[$label]['quantity'] += $quantity;
            $lines[$label]['amount'] += $amount;
        }

        $sorted = collect($lines)->sortByDesc('amount')->values()->all();

        return [
            'total' => round($total, 2),
            'formatted' => $symbol.number_format($total, 0),
            'currency' => self::str($config['currency'] ?? null, 'USD'),
            'symbol' => $symbol,
            'label' => self::str($config['label'] ?? null, 'Planning estimate'),
            'note' => self::str($config['note'] ?? null, 'Indicative only.'),
            'lines' => $sorted,
        ];
    }

    /**
     * @param  array<array-key, mixed>  $rates
     * @return RateArray
     */
    protected static function rateFor(string $type, array $rates): array
    {
        $candidate = $rates[$type] ?? $rates['generic'] ?? null;
        $rate = is_array($candidate) ? $candidate : [];

        return [
            'basis' => self::str($rate['basis'] ?? null, 'unit'),
            'rate' => self::num($rate['rate'] ?? null),
            'label' => self::str($rate['label'] ?? null, ucfirst($type)),
        ];
    }

    /**
     * Walls stand up (width x height); floors and roofs are flat (width x depth).
     *
     * @param  array<array-key, mixed>  $part
     */
    protected static function quantityFor(array $part, string $basis): float
    {
        if ($basis !== 'area') {
            return 1.0;
        }

        $width = self::num($part['width'] ?? null);
        $span = self::str($part['type'] ?? null) === 'wall'
            ? self::num($part['height'] ?? null)
            : self::num($part['depth'] ?? null);

        return max($width * $span, 0.0);
    }

    protected static function str(mixed $value, string $default = ''): string
    {
        if (is_string($value)) {
            return $value;
        }

        return is_int($value) || is_float($value) ? (string) $value : $default;
    }

    protected static function num(mixed $value): float
    {
        if (is_int($value) || is_float($value)) {
            return (float) $value;
        }

        return is_string($value) && is_numeric($value) ? (float) $value : 0.0;
    }
}
