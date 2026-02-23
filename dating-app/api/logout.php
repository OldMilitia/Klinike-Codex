<?php
/**
 * API - Cerrar sesión
 */
require_once __DIR__ . '/../includes/auth.php';

Auth::logout();
header("Location: " . BASE_URL . "/");
exit;
