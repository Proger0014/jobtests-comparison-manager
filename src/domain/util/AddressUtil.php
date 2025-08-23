<?php

namespace ComparisonManager\domain\util;

final class AddressUtil {
    private static array $tokensScore = [
        'ул',
        'пр-кт',
        'д',
        'к',
        'стр'
    ];

    private static array $removeSpace = ['д', 'к'];

    private static array $mapReplacement = [
        '^' => 'ул',
        'улица' => 'ул',
        'проспект' => 'пр-кт',
        'дом' => 'д',
        'корпус' => 'к',
        'строение' => 'стр',
        'литера' => 'стр',
        '$' => 'стр'
    ];

    public static function normalizeAddress(string $address): string {
        $target = mb_strtolower($address);

        $target = mb_ereg_replace('\.', '', $target);
        $target = mb_ereg_replace(',', '', $target);
        $target = trim($target);
        $targetArray = explode(' ', $target);
        $newArr = [];
        $startLine = self::$mapReplacement['^'];
        $endLine = self::$mapReplacement['$'];

        for ($i = 0; $i < count($targetArray); $i++) {
            $token = $targetArray[$i];

            if (array_key_exists($token, self::$mapReplacement)) {
                $replacement = self::$mapReplacement[$token];

                if (in_array($replacement, self::$removeSpace)) {
                    $nextToken = $targetArray[$i+1];

                    $replacement .= $nextToken;

                    $i++;
                }

                $newArr[] = $replacement;
            } elseif ($i == 0 && !in_array($startLine, $newArr)) {
                $replacement = $startLine;

                $newArr[] = $replacement;
                $newArr[] = $token;
            } elseif ($i == count($targetArray) - 1 && !in_array($endLine, $newArr)) {
                $replacement = $endLine;

                $newArr[] = $replacement;
                $newArr[] = $token;
            } else {
                $newArr[] = $token;
            }
        }

        $houseIndex = array_search('д', $newArr);
        $houseConcreteIndex = $houseIndex + 1;
        $houseConcrete = $newArr[$houseConcreteIndex];

        $houseParts = explode('/', $houseConcrete);

        if (count($houseParts) == 2) {
            $newArr[$houseConcreteIndex] = $houseParts[0];

            $left = array_slice($newArr, 0, $houseConcreteIndex + 1);
            $right = array_slice($newArr, $houseConcreteIndex + 1);
            $newArr = array_merge($left, ['к', $houseParts[1]], $right);
        }


        return implode(' ', $newArr);
    }

    /**
     * @param string $srcAddress нормализованный адрес с помощью {@link AddressUtil::normalizeAddress()}
     * @param string $refAddress нормализованный адрес с помощью {@link AddressUtil::normalizeAddress()}
     * @return float процент совпадения
     */
    public static function computeScore(string $srcAddress, string $refAddress): float {
        $srcAddressTokens = self::extractTokens($srcAddress);
        $refAddressTokens = self::extractTokens($refAddress);

        $intersectAddressTokens = array_intersect_assoc($srcAddressTokens, $refAddressTokens);

        return (count($intersectAddressTokens) / count($srcAddressTokens)) * 100;
    }

    private static function extractTokens(string $address): array {
        $result = [];

        $target = explode(' ', $address);

        for ($i = 0; $i < count($target);) {
            $token = $target[$i];

            if (in_array($token, self::$tokensScore)) {
                $result[$token] = [];
                $nextToken = $target[++$i] ?? null;
                while (!in_array($nextToken, self::$tokensScore) && $i < count($target)) {
                    if ($nextToken) {
                        $result[$token][] = $nextToken;
                    }

                    $i++;
                    $nextToken = $target[$i] ?? null;
                }

                $result[$token] = implode(' ', $result[$token]);
            } else {
                $i++;
            }
        }

        return $result;
    }
}