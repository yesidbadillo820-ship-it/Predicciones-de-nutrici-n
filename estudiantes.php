<?php
// estudiantes.php — Punto de entrada MVP
require_once 'includes/auth.php'; requireLogin();
require_once 'includes/db.php';
require_once 'includes/roles.php';
require_once 'presenters/EstudiantePresenter.php';

// 1. PRESENTADOR: maneja POST/GET y prepara datos
$presenter = new EstudiantePresenter($conn);
$presenter->manejarRequest();

$buscar      = $_GET['q'] ?? '';
$estudiantes = $presenter->obtenerEstudiantes($buscar);
$grados      = $presenter->obtenerGrados();

// 2. VISTA
$page_title  = 'Estudiantes';
$page_sub    = 'Gestión de estudiantes registrados';
$active_menu = 'estudiantes';
include 'includes/header.php';
include 'views/estudiantes_view.php';
include 'includes/footer.php';
