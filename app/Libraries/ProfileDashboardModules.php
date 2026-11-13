<?php

namespace App\Libraries;

use InvalidArgumentException;

class ProfileDashboardModules
{
    public static function normalizeTerritory(array $input): array
    {
        if (! isset($input['territory'], $input['seasons']) || ! is_array($input['territory']) || ! is_array($input['seasons'])) {
            throw new InvalidArgumentException('Data wilayah dan musim belum terkirim dengan lengkap.');
        }

        $content = self::asPostValues(PublicProfileContent::defaults());
        $content['territory'] = $input['territory'];
        $content['seasons'] = $input['seasons'];
        $normalized = PublicProfileContent::normalize($content);

        return [
            'territory' => $normalized['territory'],
            'seasons' => $normalized['seasons'],
        ];
    }

    public static function normalizeCensus(array $input): array
    {
        if (! isset($input['census']) || ! is_array($input['census'])) {
            throw new InvalidArgumentException('Data jumlah KK dan kelompok usia belum terkirim.');
        }

        $content = self::asPostValues(PublicProfileContent::defaults());
        $content['census'] = $input['census'];
        $normalized = PublicProfileContent::normalize($content);

        return $normalized['census'];
    }

    public static function censusSummary(array $census): array
    {
        $total = 0;
        $largestCount = 0;
        $largestGroups = [];

        foreach ($census['age_rows'] ?? [] as $row) {
            $count = (int) ($row['male'] ?? 0) + (int) ($row['female'] ?? 0);
            $total += $count;

            if ($count > $largestCount) {
                $largestCount = $count;
                $largestGroups = [$row['label'] ?? ''];
            } elseif ($count > 0 && $count === $largestCount) {
                $largestGroups[] = $row['label'] ?? '';
            }
        }

        return [
            'householdCount' => (int) ($census['household_count'] ?? 0),
            'totalJiwa' => $total,
            'largestAgeGroups' => $largestGroups,
            'largestAgeGroupCount' => $largestCount,
        ];
    }

    private static function asPostValues(array $values): array
    {
        foreach ($values as $key => $value) {
            if (is_array($value)) {
                $values[$key] = self::asPostValues($value);
            } elseif (is_bool($value)) {
                $values[$key] = $value ? '1' : '0';
            }
        }

        return $values;
    }
}
