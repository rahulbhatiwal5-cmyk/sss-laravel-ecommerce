<?php

namespace App\Services;

use InvalidArgumentException;

class CartMoney
{
    /**
     * Convert a database decimal value to an integer-minor-unit string.
     */
    public function toMinorUnits(mixed $amount): string
    {
        $amount = trim((string) $amount);

        if (preg_match('/^(\d+)(?:\.(\d{1,2}))?$/', $amount, $matches) !== 1) {
            throw new InvalidArgumentException('Expected a non-negative decimal amount with at most two decimal places.');
        }

        $whole = ltrim($matches[1], '0');
        $fraction = str_pad($matches[2] ?? '', 2, '0');

        return $this->normalize(($whole === '' ? '0' : $whole).$fraction);
    }

    public function add(string $left, string $right): string
    {
        $left = strrev($this->normalize($left));
        $right = strrev($this->normalize($right));
        $length = max(strlen($left), strlen($right));
        $carry = 0;
        $result = '';

        for ($index = 0; $index < $length; $index++) {
            $sum = (int) ($left[$index] ?? 0) + (int) ($right[$index] ?? 0) + $carry;
            $result .= (string) ($sum % 10);
            $carry = intdiv($sum, 10);
        }

        if ($carry > 0) {
            $result .= (string) $carry;
        }

        return $this->normalize(strrev($result));
    }

    public function multiply(string $minorUnits, int $quantity): string
    {
        $minorUnits = $this->normalize($minorUnits);

        if ($quantity < 0) {
            throw new InvalidArgumentException('Quantity cannot be negative.');
        }

        if ($minorUnits === '0' || $quantity === 0) {
            return '0';
        }

        $carry = 0;
        $result = '';

        for ($index = strlen($minorUnits) - 1; $index >= 0; $index--) {
            $product = ((int) ($minorUnits[$index]) * $quantity) + $carry;
            $result = (string) ($product % 10).$result;
            $carry = intdiv($product, 10);
        }

        return $this->normalize((string) $carry.$result);
    }

    public function format(string $minorUnits): string
    {
        $minorUnits = str_pad($this->normalize($minorUnits), 3, '0', STR_PAD_LEFT);
        $fraction = substr($minorUnits, -2);
        $whole = substr($minorUnits, 0, -2);
        $chunks = array_reverse(str_split(strrev($whole), 3));

        return '₹'.implode(',', array_map('strrev', $chunks)).'.'.$fraction;
    }

    private function normalize(string $minorUnits): string
    {
        if (preg_match('/^\d+$/', $minorUnits) !== 1) {
            throw new InvalidArgumentException('Minor units must be a non-negative integer string.');
        }

        $normalized = ltrim($minorUnits, '0');

        return $normalized === '' ? '0' : $normalized;
    }
}
