<?php

declare(strict_types=1);

require_once __DIR__ . '/bootstrap_env.php';

class Conexao
{
    private string $host;

    private string $dbname;

    private string $user;

    private string $pass;

    public function __construct()
    {
        loadProjectEnv();

        $this->host = (string) (getenv('DB_HOST') ?: 'localhost');
        $this->dbname = (string) (getenv('DB_NAME') ?: 'bancodechoices');
        $this->user = (string) (getenv('DB_USER') ?: 'root');
        $this->pass = (string) (getenv('DB_PASS') !== false ? getenv('DB_PASS') : '');
    }

    public function conectar(): PDO
    {
        try {
            $dsn = "mysql:host={$this->host};dbname={$this->dbname};charset=utf8mb4";

            return new PDO(
                $dsn,
                $this->user,
                $this->pass,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                ]
            );
        } catch (PDOException $e) {
            error_log('Conexao PDO: ' . $e->getMessage());
            if (PHP_SAPI !== 'cli') {
                http_response_code(503);
                exit('Serviço temporariamente indisponível. Tente novamente em instantes.');
            }
            throw new RuntimeException('Falha na conexão com a base de dados.', 0, $e);
        }
    }
}
