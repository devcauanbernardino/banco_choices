<?php

require_once __DIR__ . '/../../config/public_url.php';
require_once __DIR__ . '/../../config/conexao.php';
require_once __DIR__ . '/../Models/Usuario.php';


class LoginController
{
    private PDO $db;

    public function __construct()
    {
        // Garante que a sessão esteja iniciada
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Cria conexão com o banco
        $conexao = new Conexao();
        $this->db = $conexao->conectar();
    }

    public function handleRequest(): void
    {
        // Permite apenas POST
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect(app_url('login.php?error=acessoinvalido'));
        }

        // Captura e valida dados
        $email = trim($_POST['email'] ?? '');
        $senha = $_POST['senha'] ?? '';

        if ($email === '' || $senha === '') {
            $this->redirect(app_url('login.php?error=camposobrigatorios'));
        }

        // Autenticação
        $usuarioModel = new Usuario($this->db);
        $usuario = $usuarioModel->autenticar($email, $senha);

        if (!$usuario) {
            $this->redirect(app_url('login.php?error=logininvalido'));
        }

        if (session_status() === PHP_SESSION_ACTIVE) {
            session_regenerate_id(true);
        }

        // Matérias padrão do sistema (IDs alinhados a CriarController: 1 e 2)
        $usuarioModel->garantirMateriasParaUsuario((int) $usuario['id'], [1, 2]);
        $usuario['materias'] = $usuarioModel->buscarMateriasDoUsuario((int) $usuario['id']);

        //Sessão do usuário (PADRÃO DO SISTEMA)
        $_SESSION['usuario'] = [
            'id'    => $usuario['id'],
            'nome'  => $usuario['nome'] ?? '',
            'email' => $usuario['email'] ?? '',
            'materias' => $usuario['materias'] ?? []
        ];

        // Redireciona para o dashboard
        $this->redirect(app_url('dashboard.php'));
    }

    private function redirect(string $url): void
    {
        header('Location: ' . $url);
        exit;
    }
}

/* ================= EXECUÇÃO ================= */

$controller = new LoginController();
$controller->handleRequest();
