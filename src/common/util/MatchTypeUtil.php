<?php

namespace ComparisonManager\common\util;

use ComparisonManager\common\enum\MatchType;

final class MatchTypeUtil
{
    private static array $typesLocalization;

    public static function getTypesLocalization(): array {
        if (!isset($typesLocalization)) {
            self::$typesLocalization = self::initLocalization();
        }

        return self::$typesLocalization;
    }

    private static function initLocalization(): array {
        return [
            MatchType::auto()->getType() => 'Автоматически (%1$d%%)',
            MatchType::manual()->getType() => 'Вручную',
            MatchType::unmatched()->getType() => 'Не привязан'
        ];
    }
}