<?php

declare(strict_types=1);

/**
 * Utilizadores de teste: não recebem matérias 1 e 2 forçadas no login/sidebar
 * (para testar fluxo "Comprar matérias" com menos vínculos).
 *
 * @return list<string>
 */
function test_users_skip_default_materias(): array
{
    return [
        'teste.1materia@example.com',
    ];
}

function test_user_skips_default_materias(string $email): bool
{
    $email = strtolower(trim($email));

    return in_array($email, test_users_skip_default_materias(), true);
}
