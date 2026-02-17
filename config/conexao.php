<?php

class Conexao
{

    private $host = 'localhost';
    private $dbname = 'bancodechoices';
    private $user = 'root';
    private $pass = '';


    public function conectar()
    {
        try {
            $dsn = "mysql:host={$this->host};dbname={$this->dbname};charset=utf8mb4";

            $conexao = new PDO(
                $dsn,
                $this->user,
                $this->pass,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                ]
            );

            return $conexao;

        } catch (PDOException $e) {
            die("Erro na conexão: " . $e->getMessage());
        }
    }

}

?>