<?php
session_start();

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function isPatient() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'patient';
}

function isMedecin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'medecin';
}

function requirePatient() {
    if (!isLoggedIn() || !isPatient()) {
        header('Location: ../login.php');
        exit;
    }
}

function requireMedecin() {
    if (!isLoggedIn() || !isMedecin()) {
        header('Location: ../login.php');
        exit;
    }
}

function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: ../login.php');
        exit;
    }
}
?>