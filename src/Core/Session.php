<?php

namespace App\Core;

class Session
{
    public static function start(): void
    {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
    }

    public static function set(string $key, $value): void
    {
        self::start();
        $_SESSION[$key] = $value;
    }

    public static function get(string $key)
    {
        self::start();
        return $_SESSION[$key] ?? null;
    }

    public static function destroy(): void
    {
        self::start();
        session_destroy();
    }
    
    public static function setFlash(string $key, string $message): void
    {
        self::start();
        $_SESSION['flash'][$key] = $message;
    }
    
    public static function getFlash(string $key)
    {
        self::start();
        $message = $_SESSION['flash'][$key] ?? null;
        unset($_SESSION['flash'][$key]);
        return $message;
    }
}