<?php
// dashboard.php — Punto de entrada
// Patrón MVP: este archivo solo orquesta Presentador → Vista
require_once 'includes/auth.php'; requireLogin();
require_once 'includes/db.php';
require_once 'presenters/DashboardPresenter.php';

// 1. PRESENTADOR: obtiene y prepara los datos
$presenter   = new DashboardPresenter($conn);
$resumen     = $presenter->obtenerResumen();
$alertas     = $presenter->obtenerAlertasRecientes();
$estudiantes = $presenter->obtenerEstudiantesEnRiesgo();
$menu_hoy    = $presenter->obtenerMenuHoy();
$cobertura   = $presenter->obtenerCobertura();
$tendencia   = $presenter->obtenerTendencia();

// 2. VISTA: recibe variables y muestra HTML
$page_title = 'Panel Principal';
$page_sub   = 'Resumen general del sistema';
$active_menu = 'dashboard';
include 'includes/header.php';
include 'views/dashboard_view.php';   // ← VISTA
include 'includes/footer.php';
