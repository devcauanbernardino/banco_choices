<?php
session_start();

require_once '../config/conexao.php';
require_once '../App/Models/Usuario.php';
require_once '../App/Controllers/UsuarioController.php';

$controller->cadastrar();
?>


<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <title>Registro - BancodeChoices</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&amp;display=swap"
        rel="stylesheet" />
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f8f9fa;
        }

        .bg-navy {
            background-color: #1a237e !important;
        }

        .btn-primary {
            background-color: #1a237e !important;
            border-color: #1a237e !important;
        }

        .btn-primary:hover {
            background-color: #121858 !important;
            border-color: #121858 !important;
        }

        .text-navy {
            color: #1a237e !important;
        }

        .card {
            border: none;
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.1);
        }

        .navbar-brand {
            font-weight: 700;
            color: #1a237e !important;
        }
    </style>
</head>

<body>
    <nav class="navbar navbar-expand-lg navbar-light bg-light border-bottom">
        <div class="container">
            <a class="navbar-brand" href="#">
                <svg class="d-inline-block align-top me-2" fill="currentColor" height="30" viewBox="0 0 48 48"
                    width="30" xmlns="http://www.w3.org/2000/svg">
                    <path
                        d="M24 45.8096C19.6865 45.8096 15.4698 44.5305 11.8832 42.134C8.29667 39.7376 5.50128 36.3314 3.85056 32.3462C2.19985 28.361 1.76794 23.9758 2.60947 19.7452C3.451 15.5145 5.52816 11.6284 8.57829 8.5783C11.6284 5.52817 15.5145 3.45101 19.7452 2.60948C23.9758 1.76795 28.361 2.19986 32.3462 3.85057C36.3314 5.50129 39.7376 8.29668 42.134 11.8833C44.5305 15.4698 45.8096 19.6865 45.8096 24L24 24L24 45.8096Z">
                    </path>
                </svg>
                BancodeChoices
            </a>
            <button class="navbar-toggler" data-bs-target="#navbarNav" data-bs-toggle="collapse" type="button">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <span class="nav-link disabled text-muted">Sistema de Banco de Preguntas Médicas</span>
                    </li>
                </ul>
            </div>
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
                        <?php if (isset($_GET['error'])): ?>
                            <div class="alert alert-danger">
                                <?= htmlspecialchars($_GET['error']) ?>
                            </div>
                        <?php endif; ?>

                        <?php if (isset($_GET['success'])): ?>
                            <div class="alert alert-success">
                                <?= htmlspecialchars($_GET['success']) ?>
                            </div>
                        <?php endif; ?>

                        <form action="cadastro.php" method="POST">
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
                                <button class="btn btn-primary py-2 fw-bold" type="submit">Registrarme</button>
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
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

</body>

</html>