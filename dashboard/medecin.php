<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';
requireMedecin();
$stmt = $pdo->prepare("SELECT u.nom, u.prenom, m.specialite,
                        COUNT(DISTINCT r.id) as total_rdv,
                        COUNT(DISTINCT c.id) as total_creneaux
                        FROM users u
                        JOIN medecins m ON m.user_id = u.id
                        LEFT JOIN creneaux c ON c.medecin_id = m.id
                        LEFT JOIN rendez_vous r ON r.creneau_id = c.id
                        WHERE u.id = ?");
$stmt->execute([$_SESSION['user_id']]);
$medecin = $stmt->fetch();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>MedRDV - Espace Médecin</title>
    <style>
        * { margin:0; padding:0; box-sizing:border-box; }
        body { font-family:Arial,sans-serif; background:#f0f4f8; }
        .navbar { background:#0f6e56; padding:1rem 2rem; display:flex; justify-content:space-between; align-items:center; }
        .navbar h1 { color:white; font-size:20px; }
        .navbar a { color:white; text-decoration:none; font-size:14px; }
        .container { max-width:900px; margin:2rem auto; padding:0 1rem; }
        .welcome { background:white; border-radius:12px; padding:1.5rem; margin-bottom:1.5rem; border-left:4px solid #0f6e56; }
        .welcome h2 { color:#0f6e56; margin-bottom:0.3rem; }
        .cards { display:grid; grid-template-columns:repeat(3,1fr); gap:1rem; margin-bottom:1.5rem; }
        .card { background:white; border-radius:12px; padding:1.5rem; text-align:center; }
        .card .number { font-size:2rem; font-weight:bold; color:#0f6e56; }
        .card .label { font-size:13px; color:#666; margin-top:0.5rem; }
        .actions { display:grid; grid-template-columns:repeat(2,1fr); gap:1rem; }
        .action-btn { background:white; border-radius:12px; padding:1.5rem; text-align:center; text-decoration:none; color:#333; border:2px solid transparent; transition:all 0.2s; }
        .action-btn:hover { border-color:#0f6e56; color:#0f6e56; }
        .action-btn .icon { font-size:2rem; margin-bottom:0.5rem; }
        .action-btn p { font-size:13px; color:#666; margin-top:0.3rem; }
    </style>
</head>
<body>
<nav class="navbar">
    <h1>MedRDV</h1>
    <div>
        <span style="color:white;margin-right:1rem;">Dr. <?= htmlspecialchars($_SESSION['nom']) ?></span>
        <a href="../logout.php">Déconnexion</a>
    </div>
</nav>
<div class="container">
    <div class="welcome">
        <h2>Bonjour Dr. <?= htmlspecialchars($medecin['prenom'].' '.$medecin['nom']) ?> 👋</h2>
        <p style="color:#666;font-size:13px;"><?= htmlspecialchars($medecin['specialite']) ?></p>
    </div>
    <div class="cards">
        <div class="card"><div class="number"><?= $medecin['total_rdv'] ?></div><div class="label">Rendez-vous</div></div>
        <div class="card"><div class="number"><?= $medecin['total_creneaux'] ?></div><div class="label">Créneaux définis</div></div>
        <div class="card"><div class="number" style="color:#16a34a;">✓</div><div class="label">Compte actif</div></div>
    </div>
    <div class="actions">
        <a href="../rdv/planning.php" class="action-btn"><div class="icon">🗓️</div><strong>Gérer mon planning</strong><p>Ajouter et modifier mes créneaux</p></a>
        <a href="../rdv/mes_rdv.php" class="action-btn"><div class="icon">📋</div><strong>Mes rendez-vous</strong><p>Consulter mon planning du jour</p></a>
    </div>
</div>
</body>
</html>