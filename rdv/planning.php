<?php
require_once '../includes/db.php';
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'medecin') {
    header('Location: ../login.php'); exit;
}
$success = ''; $error = '';
$stmt = $pdo->prepare("SELECT id FROM medecins WHERE user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$medecin = $stmt->fetch();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $date = $_POST['date']; $heure_debut = $_POST['heure_debut']; $heure_fin = $_POST['heure_fin'];
    if ($heure_fin <= $heure_debut) {
        $error = "L'heure de fin doit être après l'heure de début.";
    } else {
        $stmt = $pdo->prepare("INSERT INTO creneaux (medecin_id, date, heure_debut, heure_fin, disponible) VALUES (?, ?, ?, ?, 1)");
        $stmt->execute([$medecin['id'], $date, $heure_debut, $heure_fin]);
        $success = "Créneau ajouté avec succès !";
    }
}
$stmt = $pdo->prepare("SELECT c.*, CASE WHEN r.id IS NOT NULL THEN 1 ELSE 0 END as reserve,
    u.nom as patient_nom, u.prenom as patient_prenom
    FROM creneaux c LEFT JOIN rendez_vous r ON r.creneau_id = c.id
    LEFT JOIN patients p ON p.id = r.patient_id LEFT JOIN users u ON u.id = p.user_id
    WHERE c.medecin_id = ? ORDER BY c.date DESC, c.heure_debut");
$stmt->execute([$medecin['id']]);
$creneaux = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8"><title>MedRDV - Mon Planning</title>
    <style>
        * { margin:0; padding:0; box-sizing:border-box; }
        body { font-family:Arial,sans-serif; background:#f0f4f8; }
        .navbar { background:#0f6e56; padding:1rem 2rem; display:flex; justify-content:space-between; align-items:center; }
        .navbar h1 { color:white; } .navbar a { color:white; text-decoration:none; font-size:14px; }
        .container { max-width:900px; margin:2rem auto; padding:0 1rem; }
        h2 { color:#0f6e56; margin-bottom:1.5rem; }
        .form-card { background:white; border-radius:12px; padding:1.5rem; margin-bottom:1.5rem; }
        .form-card h3 { color:#0f6e56; margin-bottom:1rem; }
        .form-row { display:grid; grid-template-columns:repeat(3,1fr); gap:1rem; margin-bottom:1rem; }
        input[type="date"], input[type="time"] { width:100%; padding:10px; border:1px solid #ddd; border-radius:8px; font-size:14px; }
        .btn { padding:10px 24px; background:#0f6e56; color:white; border:none; border-radius:8px; cursor:pointer; }
        .success { background:#dcfce7; color:#16a34a; padding:10px; border-radius:8px; margin-bottom:1rem; }
        .error { background:#fee2e2; color:#dc2626; padding:10px; border-radius:8px; margin-bottom:1rem; }
        table { width:100%; background:white; border-radius:12px; overflow:hidden; border-collapse:collapse; }
        th { background:#0f6e56; color:white; padding:12px; text-align:left; font-size:13px; }
        td { padding:12px; font-size:13px; border-bottom:1px solid #f0f4f8; }
        .badge-libre { background:#dcfce7; color:#16a34a; padding:3px 10px; border-radius:10px; font-size:12px; }
        .badge-reserve { background:#fee2e2; color:#dc2626; padding:3px 10px; border-radius:10px; font-size:12px; }
        .back { display:inline-block; margin-bottom:1rem; color:#0f6e56; text-decoration:none; font-size:14px; }
    </style>
</head>
<body>
<nav class="navbar"><h1>MedRDV</h1><a href="../logout.php">Déconnexion</a></nav>
<div class="container">
    <a href="../dashboard/medecin.php" class="back">← Retour</a>
    <h2>Mon Planning</h2>
    <?php if ($success): ?><div class="success"><?= $success ?></div><?php endif; ?>
    <?php if ($error): ?><div class="error"><?= $error ?></div><?php endif; ?>
    <div class="form-card">
        <h3>Ajouter un créneau</h3>
        <form method="POST">
            <div class="form-row">
                <div><label style="font-size:13px;color:#666;display:block;margin-bottom:4px;">Date</label><input type="date" name="date" min="<?= date('Y-m-d') ?>" required></div>
                <div><label style="font-size:13px;color:#666;display:block;margin-bottom:4px;">Heure début</label><input type="time" name="heure_debut" required></div>
                <div><label style="font-size:13px;color:#666;display:block;margin-bottom:4px;">Heure fin</label><input type="time" name="heure_fin" required></div>
            </div>
            <button type="submit" class="btn">Ajouter le créneau</button>
        </form>
    </div>
    <table>
        <thead><tr><th>Date</th><th>Début</th><th>Fin</th><th>Statut</th><th>Patient</th></tr></thead>
        <tbody>
        <?php if (empty($creneaux)): ?>
            <tr><td colspan="5" style="text-align:center;color:#666;">Aucun créneau défini</td></tr>
        <?php else: ?>
            <?php foreach ($creneaux as $c): ?>
            <tr>
                <td><?= date('d/m/Y', strtotime($c['date'])) ?></td>
                <td><?= substr($c['heure_debut'],0,5) ?></td>
                <td><?= substr($c['heure_fin'],0,5) ?></td>
                <td><?php if ($c['reserve']): ?><span class="badge-reserve">Réservé</span><?php else: ?><span class="badge-libre">Libre</span><?php endif; ?></td>
                <td><?= $c['reserve'] ? htmlspecialchars($c['patient_prenom'].' '.$c['patient_nom']) : '-' ?></td>
            </tr>
            <?php endforeach; ?>
        <?php endif; ?>
        </tbody>
    </table>
</div>
</body></html>