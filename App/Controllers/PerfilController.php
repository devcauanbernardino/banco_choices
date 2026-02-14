<?php
/**
 * ARQUIVO: PerfilController.php
 * OBJETIVO: Orquestrar as ações de atualização de perfil e segurança.
 */

class PerfilController
{
    private $userModel;
    private $session;

    public function __construct(Usuario $userModel, SimulationSession $session)
    {
        $this->userModel = $userModel;
        $this->session = $session;
    }

    /**
     * Processa a requisição de atualização de perfil
     */
    public function handleUpdate(array $postData): void
    {
        $usuario_id = $_SESSION['usuario']['id'] ?? null;

        if (!$usuario_id) {
            $this->redirect('login.php');
        }

        $nome = filter_var($postData['nome'] ?? '', FILTER_SANITIZE_SPECIAL_CHARS);
        $senhaAtual = $postData['senha_atual'] ?? '';
        $novaSenha = $postData['nova_senha'] ?? '';

        // SEGURANÇA: Se o nome vier vazio, não deixamos prosseguir
        if (empty($nome)) {
            $this->redirect('perfil.php?erro=nome_vazio');
        }

        $nome = htmlspecialchars($nome);

        try {
            // 1. Lógica de atualização de senha
            if (!empty($senhaAtual) && !empty($novaSenha)) {
                $this->processPasswordChange($usuario_id, $senhaAtual, $novaSenha);
            }

            // 2. Lógica de atualização de dados básicos
            $sucesso = $this->userModel->atualizarPerfil($usuario_id, $nome);

            if ($sucesso) {
                // Atualiza a sessão para refletir as mudanças
                $_SESSION['usuario']['nome'] = $nome;
                $this->redirect('../Views/perfil.php?sucesso=1');
            } else {
                $this->redirect('perfil.php?erro=falha_ao_salvar');
            }

        } catch (Exception $e) {
            $this->redirect('perfil.php?erro=' . $e->getMessage());
        }
    }

    /**
     * Valida e altera a senha do usuário
     */
    private function processPasswordChange(int $usuario_id, string $atual, string $nova): void
    {
        $dadosUsuario = $this->userModel->buscarPorId($usuario_id);

        if (!password_verify($atual, $dadosUsuario['senha'])) {
            throw new Exception('senha_incorreta');
        }

        if (strlen($nova) < 8) {
            throw new Exception('senha_curta');
        }

        $novaSenhaHash = password_hash($nova, PASSWORD_DEFAULT);
        $this->userModel->atualizarSenha($usuario_id, $novaSenhaHash);
    }

    /**
     * Redirecionamento centralizado
     */
    private function redirect(string $url): void
    {
        header("Location: $url");
        exit;
    }
}
