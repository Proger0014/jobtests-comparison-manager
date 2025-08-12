<?php

namespace ComparisonManager\common\enum;


use yii\base\InvalidArgumentException;

final class MatchType
{
    private string $type;

    public function getType(): string {
        return $this->type;
    }

    private function __construct(string $type) {
        $this->type = $type;
    }

    private static array $types = ['auto', 'manual', 'unmatched'];

    public static function from(string $type): MatchType {
        if (!in_array($type, self::$types)) {
            throw new InvalidArgumentException("$type не существует для MatchType");
        }

        return new self($type);
    }

    public static function auto(): MatchType {
        return new self('auto');
    }

    public static function manual(): MatchType {
        return new self('manual');
    }

    public static function unmatched(): MatchType {
        return new self('unmatched');
    }
}