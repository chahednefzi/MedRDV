<?php

if (isset($_SESSION['user_id'])) {
    if ($_SESSION['role'] === 'medecin') {
        header('Location: dashboard/medecin.php');
    } else {
        header('Location: dashboard/patient.php');
    }
    exit;
}
header('Location: login.php');
exit;
?>