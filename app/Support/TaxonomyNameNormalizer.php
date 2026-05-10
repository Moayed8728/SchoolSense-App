<?php

namespace App\Support;

use Illuminate\Support\Str;

class TaxonomyNameNormalizer
{
    private const CANONICAL_NAMES = [
        'american' => 'American',
        'british' => 'British',
        'ib' => 'IB',
        'international baccalaureate ib' => 'IB',
        'international baccalaureate' => 'IB',
        'coding club' => 'Coding Club',
        'robotics' => 'Robotics',
        'stem lab' => 'STEM Lab',
        'arabic' => 'Arabic',
        'english' => 'English',
    ];

    public static function split(?string $value): array
    {
        if (!$value) {
            return [];
        }

        return collect(preg_split('/[|\r\n]+/', $value) ?: [])
            ->map(fn (string $item) => self::normalize($item))
            ->filter()
            ->unique(fn (string $item) => self::key($item))
            ->values()
            ->all();
    }

    public static function normalize(mixed $value): string
    {
        $value = self::sanitize($value);

        if ($value === '') {
            return '';
        }

        if (preg_match('/[|\r\n]/', $value)) {
            $parts = collect(preg_split('/[|\r\n]+/', $value) ?: [])
                ->map(fn (string $item) => self::normalize($item))
                ->filter()
                ->unique(fn (string $item) => self::key($item))
                ->values();

            if ($parts->count() === 1) {
                return $parts->first();
            }
        }

        $value = preg_replace('/\s+/u', ' ', $value) ?? $value;
        $value = trim($value);

        do {
            $previous = $value;
            $value = trim($value, " \t\n\r\0\x0B'\"`“”‘’");
        } while ($value !== $previous);

        $value = preg_replace('/\s+/u', ' ', $value) ?? $value;
        $value = trim($value);

        if ($value === '') {
            return '';
        }

        return self::CANONICAL_NAMES[self::key($value)] ?? $value;
    }

    public static function key(mixed $value): string
    {
        $value = self::sanitize($value);
        $value = Str::ascii($value);
        $value = Str::lower($value);
        $value = preg_replace('/[^\pL\pN]+/u', ' ', $value) ?? $value;

        return trim(preg_replace('/\s+/u', ' ', $value) ?? $value);
    }

    public static function slug(string $name): string
    {
        return Str::slug($name) ?: (string) Str::uuid();
    }

    private static function sanitize(mixed $value): string
    {
        $value = (string) $value;

        if ($value === '') {
            return '';
        }

        if (function_exists('mb_check_encoding') && !mb_check_encoding($value, 'UTF-8')) {
            $value = mb_convert_encoding($value, 'UTF-8', 'Windows-1252');
        }

        $cleaned = @preg_replace('/[^\P{C}\t\r\n]+/u', ' ', $value);

        return trim($cleaned ?? $value);
    }
}
