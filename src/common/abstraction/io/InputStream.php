<?php

namespace ComparisonManager\common\abstraction\io;

interface InputStream
{
    /**
     * Абстракция для загрузки конкретного файла построчно
     *
     * @param string $path
     * @param callable<mixed> $lineConsumer
     */
    function load(string $path, callable $lineConsumer): void;
}