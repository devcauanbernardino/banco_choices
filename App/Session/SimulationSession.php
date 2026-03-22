<?php

/**
 * Estado do simulado na sessão PHP (única definição; evita divergência entre controllers).
 */
class SimulationSession
{
    private const SESSION_KEY = 'simulado';

    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    public function init($data): void
    {
        $_SESSION[self::SESSION_KEY] = $data;
    }

    public function isActive(): bool
    {
        return isset($_SESSION[self::SESSION_KEY]);
    }

    public function set(string $key, $value): void
    {
        $_SESSION[self::SESSION_KEY][$key] = $value;
    }

    /**
     * @return mixed|null
     */
    public function get(string $key)
    {
        if (!isset($_SESSION[self::SESSION_KEY])) {
            return null;
        }

        return $_SESSION[self::SESSION_KEY][$key] ?? null;
    }

    public function clear(): void
    {
        unset($_SESSION[self::SESSION_KEY]);
    }
}
