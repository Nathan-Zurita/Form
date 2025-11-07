<?php
/**
 * Configurações de Sessão Segura
 * Configurações para melhorar a segurança das sessões PHP
 */

// Iniciar configurações de sessão apenas se não estiver ativa
if (session_status() == PHP_SESSION_NONE) {
    // Configurações de segurança da sessão
    ini_set('session.cookie_httponly', 1);     // Impede acesso via JavaScript
    ini_set('session.use_only_cookies', 1);    // Usar apenas cookies para sessão
    ini_set('session.cookie_secure', 0);       // Mudar para 1 se usando HTTPS
    ini_set('session.entropy_length', 32);     // Aumentar entropia
    ini_set('session.hash_function', 'sha256'); // Usar hash mais forte

    // Tempo de vida da sessão (8 horas)
    ini_set('session.gc_maxlifetime', 28800);
    ini_set('session.cookie_lifetime', 28800);
    
    // Nome da sessão personalizado
    session_name('SINDPPENAL_ADMIN_SESSION');
    
    // Iniciar sessão
    session_start();
    
    // Regenerar ID da sessão para prevenir fixação
    if (!isset($_SESSION['session_regenerated'])) {
        session_regenerate_id(true);
        $_SESSION['session_regenerated'] = true;
    }
    
    if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > 28800)) {
        session_unset();
        session_destroy();
        session_start();
    }
    $_SESSION['last_activity'] = time();
    $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
    if (isset($_SESSION['user_agent']) && $_SESSION['user_agent'] !== $user_agent) {
        session_unset();
        session_destroy();
        session_start();
    }
    $_SESSION['user_agent'] = $user_agent;
}

