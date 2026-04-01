<?php
require_once '../includes/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'patient') {
    header('Location: ../login.php'); exit;
}
$stmt = $pdo->prepare("SELECT u.nom, u.prenom, COUNT(r.id) as total_rdv 
                        FROM users u 
                        LEFT JOIN patients p ON p.user_id = u.id
                        LEFT JOIN rendez_vous r ON r.patient_id = p.id
                        WHERE u.id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>MedRDV - Mon Espace</title>
    <style>
        * { margin:0; padding:0; box-sizing:border-box; }
        body { font-family:Arial,sans-serif; background:#f0f4f8; }
        .navbar { background:#2563eb; padding:1rem 2rem; display:flex; justify-content:space-between; align-items:center; }
        .navbar h1 { color:white; font-size:20px; }
        .navbar a { color:white; text-decoration:none; font-size:14px; }
        .container { max-width:900px; margin:2rem auto; padding:0 1rem; }
        .welcome { background:white; border-radius:12px; padding:1.5rem; margin-bottom:1.5rem; border-left:4px solid #2563eb; }
        .welcome h2 { color:#2563eb; margin-bottom:0.5rem; }
        .cards { display:grid; grid-template-columns:repeat(3,1fr); gap:1rem; margin-bottom:1.5rem; }
        .card { background:white; border-radius:12px; padding:1.5rem; text-align:center; }
        .card .number { font-size:2rem; font-weight:bold; color:#2563eb; }
        .card .label { font-size:13px; color:#666; margin-top:0.5rem; }
        .actions { display:grid; grid-template-columns:repeat(2,1fr); gap:1rem; }
        .action-btn { background:white; border-radius:12px; padding:1.5rem; text-align:center; text-decoration:none; color:#333; border:2px solid transparent; transition:all 0.2s; }
        .action-btn:hover { border-color:#2563eb; color:#2563eb; }
        .action-btn .icon { font-size:2rem; margin-bottom:0.5rem; }
        .action-btn p { font-size:13px; color:#666; margin-top:0.3rem; }
    </style>
</head>
<body>
<nav class="navbar">
    <h1>MedRDV</h1>
    <div>
        <span style="color:white;margin-right:1rem;">👤 <?= htmlspecialchars($_SESSION['nom']) ?></span>
        <a href="../logout.php">Déconnexion</a>
    </div>
</nav>
<div class="container">
    <div class="welcome">
        <h2>Bonjour <?= htmlspecialchars($user['prenom'].' '.$user['nom']) ?> 👋</h2>
        <p style="color:#666;font-size:14px;">Bienvenue sur votre espace patient MedRDV</p>
    </div>
    <div class="cards">
        <div class="card"><div class="number"><?= $user['total_rdv'] ?></div><div class="label">Rendez-vous total</div></div>
        <div class="card"><div class="number" style="color:#16a34a;">✓</div><div class="label">Compte actif</div></div>
        <div class="card"><div class="number" style="color:#f59e0b;">!</div><div class="label">Notifications</div></div>
    </div>
    <div class="actions">
        <a href="../rdv/prendre.php" class="action-btn"><div class="icon">📅</div><strong>Prendre un RDV</strong><p>Choisir un médecin et un créneau</p></a>
        <a href="../rdv/mes_rdv.php" class="action-btn"><div class="icon">📋</div><strong>Mes rendez-vous</strong><p>Voir et gérer mes RDV</p></a>
    </div>
</div>
</body>
</html>