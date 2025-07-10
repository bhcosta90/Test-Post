<?php

declare(strict_types = 1);

namespace QuantumTecnology\ControllerQraphQLExtension\Support;

final class LogSupport
{
    private static array $messages = [];

    public static function add($message): void
    {
        if (app()->isLocal()) {
            self::$messages[] = $message;
        }
    }

    public static function getMessages(): array
    {
        return self::$messages;
    }
}
