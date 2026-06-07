<?php
// includes/auth.php
session_start();

function isLoggedIn() {
    return isset($_SESSION['usuario_id']);
}

function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit;
    }
}

function getUsuario() {
    return $_SESSION['usuario'] ?? null;
}

function logout() {
    session_destroy();
    header('Location: login.php');
    exit;
}
