<?php
session_start();
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

        // Passamos os dados para a validação
        $this->validarDados($dados);

        if ($this->usuario->buscarPorEmail($dados['email'])) {
            // CORREÇÃO: Passar $dados aqui
            $this->redirect('emailcadastrado', $dados);
        }

        // Se o seu Model já faz o password_hash (como vimos no código anterior), 
        // cuidado para não fazer o hash duas vezes. 
        // Se o Model Usuario->cadastrar faz hash, mande a senha limpa.
        
        $sucesso = $this->usuario->cadastrarComMaterias(
            $dados['nome'],
            $dados['email'],
            $dados['senha'], 
            $dados['materias']
        );

        if (!$sucesso) {
            $this->redirect('error', $dados); // CORREÇÃO: Passar $dados aqui
        }

        // Se deu certo, limpamos a sessão antes de ir para o sucesso
        unset($_SESSION['old_input']);
        header('Location: /banco_choices/public/cadastro.php?success=success');
        exit;
    }

    private function getDadosFormulario()
    {
        return [
            'nome' => trim($_POST['nome'] ?? ''),
            'email' => trim($_POST['email'] ?? ''),
            'senha' => $_POST['senha'] ?? '',
            'confirmaSenha' => $_POST['confirma-senha'] ?? '',
            'materias' => $_POST['materias'] ?? []
        ];
    }

    private function validarDados(array $dados)
    {
        // CORREÇÃO EM TODAS AS CHAMADAS ABAIXO: Adicionado , $dados
        if (
            $dados['nome'] === '' ||
            $dados['email'] === '' ||
            $dados['senha'] === '' ||
            $dados['confirmaSenha'] === '' ||
            $dados['materias'] === []
        ) {
            $this->redirect('camposobrigatorios', $dados);
        }

        if (!filter_var($dados['email'], FILTER_VALIDATE_EMAIL)) {
            $this->redirect('emailinvalido', $dados);
        }

        if ($dados['senha'] !== $dados['confirmaSenha']) {
            $this->redirect('naocoincidem', $dados);
        }

        $senha = $dados['senha'];
        $comprimentoMinimo = strlen($senha) >= 8;
        $temMaiscula = preg_match('/[A-Z]/', $senha);
        $temMinuscula = preg_match('/[a-z]/', $senha);
        $temNumero = preg_match('/[0-9]/', $senha);
        $temEspecial = preg_match('/[\W_]/', $senha);

        if (!$comprimentoMinimo || !$temMaiscula || !$temMinuscula || !$temNumero || !$temEspecial) {
            $this->redirect('senhafraca', $dados);
        }
    }

    private function redirect($error, $dadosParaSalvar = []): void
    {
        if (!empty($dadosParaSalvar)) {
            $_SESSION['old_input'] = [
                'nome' => $dadosParaSalvar['nome'] ?? '',
                'email' => $dadosParaSalvar['email'] ?? '',
                'materias' => $dadosParaSalvar['materias'] ?? []
            ];
            // Garante que a sessão seja gravada antes do redirecionamento
            session_write_close();
        }
        header("Location: /banco_choices/public/cadastro.php?error={$error}");
        exit;
    }
}

$controller = new UsuarioController();
$controller->cadastrar();