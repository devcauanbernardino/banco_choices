<?php
$messages = [
    'error' => [
        'acessoinvalido' => 'Acesso inválido.',
        'camposobrigatorios' => 'Preencha todos os campos.',
        'emailinvalido' => 'Informe um e-mail válido.',
        'naocoincidem' => 'As senhas não coincidem.',
        'emailcadastrado' => 'Este e-mail já está cadastrado.'
    ],
    'success' => [
        'success' => 'Conta criada com sucesso!'
    ]
];

// captura os códigos da URL
$errorCode = $_GET['error'] ?? null;
$successCode = $_GET['success'] ?? null;
?>

<!DOCTYPE html>
<html lang="es-AR">

<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <title>Banco de Choices | Registro</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&amp;display=swap"
        rel="stylesheet" />
    <link rel="stylesheet" href="../App/assets/css/cadastro.css" />
</head>

<body>
    <nav class="navbar navbar-expand-lg navbar-light bg-light border-bottom">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                 <img src="../App/assets/img/logo-bd-transparente.png" alt="logo" style="width: 60px; height: 60px;"/>
            </a>
            <button class="navbar-toggler" data-bs-target="#navbarNav" data-bs-toggle="collapse" type="button">
                <span class="navbar-toggler-icon"></span>
            </button>
        </div>
    </nav>
    <main class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-5">
                <div class="card">
                    <div class="card-header bg-white border-bottom-0 pt-4 pb-0 text-center">
                        <h4 class="fw-bold">Creá tu cuenta en BancodeChoices</h4>
                        <p class="text-muted small">Sumate a la plataforma de estudio líder</p>
                    </div>
                    <div class="card-body p-4">
                        <?php if ($errorCode && isset($messages['error'][$errorCode])): ?>
                            <div class="alert alert-danger text-center">
                                <?= $messages['error'][$errorCode] ?>
                            </div>
                        <?php endif; ?>

                        <form action="../App/Controllers/UsuarioController.php" method="POST">
                            <div class="mb-3">
                                <label class="form-label" for="name">Nombre Completo</label>
                                <input class="form-control" id="name" name="nome" placeholder="Ej: Juan Pérez"
                                    required="" type="text" />
                            </div>
                            <div class="mb-3">
                                <label class="form-label" for="email">Correo Electrónico</label>
                                <input class="form-control" id="email" name="email" placeholder="ejemplo@mail.com"
                                    required="" type="email" />
                            </div>
                            <div class="mb-3">
                                <label class="form-label" for="password">Contraseña</label>
                                <input class="form-control" id="password" name="senha" placeholder="••••••••"
                                    required="" type="password" />
                            </div>
                            <div class="mb-3">
                                <label class="form-label" for="confirm-password">Confirmar Contraseña</label>
                                <input class="form-control" id="confirm-password" name="confirma-senha"
                                    placeholder="••••••••" required="" type="password" />
                            </div>
                            <div class="mb-4">
                                <div class="form-check">
                                    <input class="form-check-input" id="terms" name="terms" required=""
                                        type="checkbox" />
                                    <label class="form-check-label small text-muted" for="terms">
                                        Acepto los <a class="text-navy text-decoration-none fw-bold" href="#">términos y
                                            condiciones</a> y la política de privacidad.
                                    </label>
                                </div>
                            </div>
                            <div class="d-grid">
                                <button class="btn py-2 fw-bold" type="submit">Registrarme</button>
                            </div>
                        </form>
                        <div class="mt-4 pt-3 border-top text-center">
                            <p class="mb-0 small">
                                ¿Ya tenés una cuenta?
                                <a class="text-navy fw-bold text-decoration-none" href="login.php">Iniciá sesión</a>
                            </p>
                        </div>
                    </div>
                </div>
                <div class="mt-4 text-center">
                    <p class="text-muted small">
                        © 2026 BancodeChoices. Todos los derechos reservados.
                    </p>
                </div>
            </div>
        </div>
    </main>

    <!-- Modal de Sucesso -->
    <div class="modal fade" id="successModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title">Conta criada com sucesso</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body text-center py-4">
                    <p class="fs-5 mb-2">
                        <?= $messages['success'][$successCode] ?? '' ?>
                    </p>

                </div>
                <div class="modal-footer justify-content-center">
                    <a href="login.php" class="btn btn-success px-4">
                        Ir para o login
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        <?php if (isset($_GET['success'])): ?>
            const successModal = new bootstrap.Modal(
                document.getElementById('successModal')
            );
            successModal.show();
        <?php endif; ?>
    </script>


</body>

</html>