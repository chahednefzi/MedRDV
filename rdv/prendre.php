<?php
require_once '../includes/db.php';
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'patient') {
    header('Location: ../login.php'); exit;
}
$success = ''; $error = '';
$stmt = $pdo->prepare("SELECT id FROM patients WHERE user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$patient = $stmt->fetch();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $creneau_id = $_POST['creneau_id'];
    $stmt = $pdo->prepare("SELECT * FROM creneaux WHERE id = ? AND disponible = 1");
    $stmt->execute([$creneau_id]);
    $creneau = $stmt->fetch();
    if ($creneau) {
        $stmt = $pdo->prepare("INSERT INTO rendez_vous (patient_id, creneau_id, statut) VALUES (?, ?, 'confirme')");
        $stmt->execute([$patient['id'], $creneau_id]);
        $stmt = $pdo->prepare("UPDATE creneaux SET disponible = 0 WHERE id = ?");
        $stmt->execute([$creneau_id]);
        $success = "Rendez-vous confirmé avec succès !";
    } else {
        $error = "Ce créneau n'est plus disponible.";
    }
}
$creneaux = $pdo->query("SELECT c.id, c.date, c.heure_debut, c.heure_fin, u.nom, u.prenom, m.specialite
    FROM creneaux c JOIN medecins m ON m.id = c.medecin_id JOIN users u ON u.id = m.user_id
    WHERE c.disponible = 1 AND c.date >= CURDATE() ORDER BY c.date, c.heure_debut")->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8"><title>MedRDV - Prendre un RDV</title>
    <style>
        * { margin:0; padding:0; box-sizing:border-box; }
        body { font-family:Arial,sans-serif; background:#f0f4f8; }
        .navbar { background:#2563eb; padding:1rem 2rem; display:flex; justify-content:space-between; align-items:center; }
        .navbar h1 { color:white; } .navbar a { color:white; text-decoration:none; font-size:14px; }
        .container { max-width:900px; margin:2rem auto; padding:0 1rem; }
        h2 { color:#2563eb; margin-bottom:1.5rem; }
        .success { background:#dcfce7; color:#16a34a; padding:10px; border-radius:8px; margin-bottom:1rem; }
        .error { background:#fee2e2; color:#dc2626; padding:10px; border-radius:8px; margin-bottom:1rem; }
        .creneaux { display:grid; grid-template-columns:repeat(2,1fr); gap:1rem; }
        .creneau-card { background:white; border-radius:12px; padding:1.5rem; border:2px solid transparent; }
        .creneau-card:hover { border-color:#2563eb; }
        .medecin-name { font-weight:bold; color:#333; margin-bottom:0.3rem; }
        .specialite { font-size:12px; color:#2563eb; background:#eff6ff; padding:3px 8px; border-radius:10px; display:inline-block; margin-bottom:0.8rem; }
        .date-heure { font-size:14px; color:#666; margin-bottom:1rem; }
        .btn { width:100%; padding:10px; background:#2563eb; color:white; border:none; border-radius:8px; cursor:pointer; }
        .empty { background:white; border-radius:12px; padding:2rem; text-align:center; color:#666; }
        .back { display:inline-block; margin-bottom:1rem; color:#2563eb; text-decoration:none; font-size:14px; }
    </style>
</head>
<body>
<nav class="navbar"><h1>MedRDV</h1><a href="../logout.php">Déconnexion</a></nav>
<div class="container">
    <a href="../dashboard/patient.php" class="back">← Retour</a>
    <h2>Prendre un rendez-vous</h2>
    <?php if ($success): ?><div class="success"><?= $success ?></div><?php endif; ?>
    <?php if ($error): ?><div class="error"><?= $error ?></div><?php endif; ?>
    <?php if (empty($creneaux)): ?>
        <div class="empty">Aucun créneau disponible pour le moment.</div>
    <?php else: ?>
    <div class="creneaux">
        <?php foreach ($creneaux as $c): ?>
        <div class="creneau-card">
            <div class="medecin-name">Dr. <?= htmlspecialchars($c['prenom'].' '.$c['nom']) ?></div>
            <span class="specialite"><?= htmlspecialchars($c['specialite']) ?></span>
            <div class="date-heure">📅 <?= date('d/m/Y', strtotime($c['date'])) ?><br>🕐 <?= substr($c['heure_debut'],0,5) ?> - <?= substr($c['heure_fin'],0,5) ?></div>
            <form method="POST">
                <input type="hidden" name="creneau_id" value="<?= $c['id'] ?>">
                <button type="submit" class="btn">Réserver ce créneau</button>
            </form>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</div>
</body></html>