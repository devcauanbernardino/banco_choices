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

     /**
     * Busca os dados completos de um usuário pelo ID
     */
    public function buscarPorId($id) {
        $sql = "SELECT * FROM users WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

     /**
     * Atualiza os dados básicos do perfil
     */
    public function atualizarPerfil($id, $nome) {
        $sql = "UPDATE users SET nome = :nome WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([
            ':nome' => $nome,
            ':id'   => $id
        ]);
    }

     /**
     * Atualiza a senha do usuário
     */
    public function atualizarSenha($id, $novaSenhaHash) {
        $sql = "UPDATE users SET senha = :senha WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([
            ':senha' => $novaSenhaHash,
            ':id'    => $id
        ]);
    }


}


?>