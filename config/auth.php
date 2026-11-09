<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!defined('PETCARE_BASE_URL')) {
    $script = str_replace('\\\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
    define('PETCARE_BASE_URL', (preg_match('#^/PetCare(?:/|$)#i', $script) ? '/PetCare' : ''));
}
function petcare_logged_in(): bool {
    return !empty($_SESSION['petcare_user']) && in_array(($_SESSION['petcare_user']['role'] ?? ''), ['admin','funcionario'], true);
}
function petcare_require_login(): void {
    if (!petcare_logged_in()) {
        header('Location: ' . PETCARE_BASE_URL . '/login.php');
        exit;
    }
}
function petcare_admin_name(): string {
    return $_SESSION['petcare_user']['name'] ?? 'Administrador';
}

function petcare_csrf_token(): string {
    if(empty($_SESSION['petcare_csrf'])) $_SESSION['petcare_csrf']=bin2hex(random_bytes(32));
    return $_SESSION['petcare_csrf'];
}
function petcare_flash(string $message, string $kind='error'): void {
    $_SESSION['petcare_flash']=['message'=>$message,'kind'=>$kind];
}
function petcare_back(string $message, string $kind='error'): void {
    petcare_flash($message,$kind);
    $fallback=PETCARE_BASE_URL.'/index.php';
    $referer=$_SERVER['HTTP_REFERER']??'';
    $path=parse_url($referer,PHP_URL_PATH);
    $host=parse_url($referer,PHP_URL_HOST);
    $localHost=$_SERVER['HTTP_HOST']??'';
    $target=($path && $host && strcasecmp($host,explode(':',$localHost)[0])===0 && str_starts_with($path,PETCARE_BASE_URL.'/'))?$path:$fallback;
    header('Location: '.$target, true, 303);
    exit;
}
function petcare_check_csrf(): void {
    if(!hash_equals(petcare_csrf_token(),(string)($_POST['csrf_token']??''))){petcare_back('Formulário expirado. Atualize a página e tente novamente.');}
}

function petcare_require_admin(): void {
    petcare_require_login();
    if (($_SESSION['petcare_user']['role'] ?? '') !== 'admin') {petcare_back('Você não tem autorização para esta operação.');}
}
