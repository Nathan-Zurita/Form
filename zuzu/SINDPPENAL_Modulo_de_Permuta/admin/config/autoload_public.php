<?php 
/**
 * Autoload para uso público (sem configurações de sessão administrativa)
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/database.php';

// NÃO incluir session.php para evitar conflitos com sessão pública
// require_once __DIR__ . '/session.php';

require_once __DIR__ . '/../classes/AdminContatos.php';