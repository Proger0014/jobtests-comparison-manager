<?php

namespace ComparisonManager\domain\service;

use ComparisonManager\common\abstraction\io\InputStream;
use Yii;
use yii\base\ErrorException;
use yii\base\InvalidArgumentException;

class CsvInputStream implements InputStream
{
    /**
     * <p>На основе `$path` считывает csv файл построчно,
     * складывая каждую строку в `$lineConsumer`
     *
     * @throws InvalidArgumentException
     */
    function load(string $path, callable $lineConsumer): void
    {
        Yii::debug("Открытие файла $path", __METHOD__);

        $file = null;

        try {
            $file = fopen($path, 'r');
        } catch (ErrorException $e) {
            Yii::error("Отсутствие файла $path", __METHOD__);
            throw new InvalidArgumentException("Такого файла не существует $path");
        }

        Yii::debug("Начало построчного чтения $file", __METHOD__);

        while ($line = fgetcsv($file)) {
            call_user_func($lineConsumer, $line);
        }

        Yii::debug("Построчное чтение $file завершено!", __METHOD__);
    }
}