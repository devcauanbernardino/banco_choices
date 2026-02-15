<?php

require_once __DIR__ . '/../../config/conexao.php';
require_once __DIR__ . '/../Models/Usuario.php';

class UsuarioController
{
    private Usuario $usuario;

    public function __construct()
    {
        $database = new Conexao();
        $conn = $database->conectar();

        $this->usuario = new Usuario($conn);
    }

    public function cadastrar(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('acessoinvalido');
        }

        $dados = $this->getDadosFormulario();

        $this->validarDados($dados);

        if ($this->usuario->buscarPorEmail($dados['email'])) {
            $this->redirect('emailcadastrado');
        }

        $sucesso = $this->usuario->cadastrarComMaterias(
            $dados['nome'],
            $dados['email'],
            $dados['senha'],
            $dados['materias']
        );

        if (!$sucesso) {
            $this->redirect('error');
        }

        header('Location: /banco_choices/public/cadastro.php?success=success');
        exit;
    }

    /* ======================
       MÉTODOS AUXILIARES
    ====================== */

    private function getDadosFormulario(): array
    {
        return [
            'nome'           => trim($_POST['nome'] ?? ''),
            'email'          => trim($_POST['email'] ?? ''),
            'senha'          => $_POST['senha'] ?? '',
            'confirmaSenha'  => $_POST['confirma-senha'] ?? '',
            'materias'       => $_POST['materias'] ?? []
        ];
    }

    private function validarDados(array $dados): void
    {
        if (
            $dados['nome'] === '' ||
            $dados['email'] === '' ||
            $dados['senha'] === '' ||
            $dados['confirmaSenha'] === '' ||
            $dados['materias'] === []
        ) {
            $this->redirect('camposobrigatorios');
        }

        if (!filter_var($dados['email'], FILTER_VALIDATE_EMAIL)) {
            $this->redirect('emailinvalido');
        }

        if ($dados['senha'] !== $dados['confirmaSenha']) {
            $this->redirect('naocoincidem');
        }
    }

    private function redirect($error): void
    {
        header("Location: /banco_choices/public/cadastro.php?error={$error}");
        exit;
    }
}

/* ======================
   EXECUÇÃO
====================== */

$controller = new UsuarioController();
$controller->cadastrar();
