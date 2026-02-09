<?php

class Usuario
{
    private $conn;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    //metodo para cadastrar um novo usuario
    public function cadastrar($nome, $email, $senha)
    {
        $senhaHash = password_hash($senha, PASSWORD_DEFAULT);
        $sql = "INSERT INTO users (nome, email, senha) VALUES (:nome, :email, :senha)";

        $stmt = $this->conn->prepare($sql);

        return $stmt->execute([
            ':nome' => $nome,
            ':email' => $email,
            ':senha' => $senhaHash
        ]);
    }

    //buscar usuario pelo email
    public function buscarPorEmail($email){
        $sql = "SELECT * FROM users WHERE email = :email LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':email' => $email]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    //validar o login
    public function autenticar($email, $senha) {
        $usuario = $this->buscarPorEmail($email);

        if (!$usuario) {
            return false;
        }

        if (password_verify($senha, $usuario['senha'])) {
            return $usuario;
        }

        return false;
    }
}


?>