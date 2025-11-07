<?php
/**
 * Redirecionamento automático para login do admin
 * Acesso: /admin ou /admin/
 */

// Verifica se já está logado
session_start();
if (isset($_SESSION['admin_logado']) && $_SESSION['admin_logado'] === true) {
    // Se já está logado, vai direto para o dashboard
    header('Location: dashboard.php');
} else {
    // Se não está logado, vai para o login
    header('Location: login.php');
}
exit();
?>