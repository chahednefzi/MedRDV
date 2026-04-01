<?php
require_once '../includes/db.php';

if (!isset($_SESSION['user_id'])) { header('Location: ../login.php'); exit; }
$success = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_SESSION['role'] === 'patient') {
    $stmt = $pdo->prepare("UPDATE rendez_vous SET statut = 'annule' WHERE id = ?");
    $stmt->execute([$_POST['rdv_id']]);
    $stmt = $pdo->prepare("UPDATE creneaux SET disponible = 1 WHERE id = ?");
    $stmt->execute([$_POST['creneau_id']]);
    $success = "Rendez-vous annulé avec succès.";
}
if ($_SESSION['role'] === 'patient') {
    $stmt = $pdo->prepare("SELECT r.id, r.statut, c.date, c.heure_debut, c.heure_fin, c.id as creneau_id,
        u.nom as medecin_nom, u.prenom as medecin_prenom, m.specialite
        FROM rendez_vous r JOIN creneaux c ON c.id = r.creneau_id
        JOIN medecins m ON m.id = c.medecin_id JOIN users u ON u.id = m.user_id
        JOIN patients p ON p.id = r.patient_id WHERE p.user_id = ? ORDER BY c.date DESC");
    $stmt->execute([$_SESSION['user_id']]);
} else {
    $stmt = $pdo->prepare("SELECT r.id, r.statut, c.date, c.heure_debut, c.heure_fin, c.id as creneau_id,
        u.nom as patient_nom, u.prenom as patient_prenom
        FROM rendez_vous r JOIN creneaux c ON c.id = r.creneau_id
        JOIN medecins m ON m.id = c.medecin_id JOIN patients p ON p.id = r.patient_id
        JOIN users u ON u.id = p.user_id WHERE m.user_id = ? ORDER BY c.date DESC");
    $stmt->execute([$_SESSION['user_id']]);
}
$rdvs = $stmt->fetchAll();
$is_patient = $_SESSION['role'] === 'patient';
$color = $is_patient ? '#2563eb' : '#0f6e56';
$back = $is_patient ? '../dashboard/patient.php' : '../dashboard/medecin.php';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8"><title>MedRDV - Mes RDV</title>
    <style>
        * { margin:0; padding:0; box-sizing:border-box; }
        body { font-family:Arial,sans-serif; background:#f0f4f8; }
        .navbar { background:<?= $color ?>; padding:1rem 2rem; display:flex; justify-content:space-between; align-items:center; }
        .navbar h1 { color:white; } .navbar a { color:white; text-decoration:none; font-size:14px; }
        .container { max-width:900px; margin:2rem auto; padding:0 1rem; }
        h2 { color:<?= $color ?>; margin-bottom:1.5rem; }
        .success { background:#dcfce7; color:#16a34a; padding:10px; border-radius:8px; margin-bottom:1rem; }
        table { width:100%; background:white; border-radius:12px; overflow:hidden; border-collapse:collapse; }
        th { background:<?= $color ?>; color:white; padding:12px; text-align:left; font-size:13px; }
        td { padding:12px; font-size:13px; border-bottom:1px solid #f0f4f8; }
        .badge-confirme { background:#dcfce7; color:#16a34a; padding:3px 10px; border-radius:10px; font-size:12px; }
        .badge-annule { background:#fee2e2; color:#dc2626; padding:3px 10px; border-radius:10px; font-size:12px; }
        .btn-annuler { padding:5px 12px; background:#fee2e2; color:#dc2626; border:none; border-radius:6px; cursor:pointer; font-size:12px; }
        .empty { background:white; border-radius:12px; padding:2rem; text-align:center; color:#666; }
        .back { display:inline-block; margin-bottom:1rem; color:<?= $color ?>; text-decoration:none; font-size:14px; }
    </style>
</head>
<body>
<nav class="navbar"><h1>MedRDV</h1><a href="../logout.php">Déconnexion</a></nav>
<div class="container">
    <a href="<?= $back ?>" class="back">← Retour</a>
    <h2>Mes Rendez-vous</h2>
    <?php if ($success): ?><div class="success"><?= $success ?></div><?php endif; ?>
    <?php if (empty($rdvs)): ?>
        <div class="empty">Aucun rendez-vous trouvé.</div>
    <?php else: ?>
    <table>
        <thead><tr>
            <th>Date</th><th>Heure</th>
            <th><?= $is_patient ? 'Médecin' : 'Patient' ?></th>
            <?php if ($is_patient): ?><th>Spécialité</th><?php endif; ?>
            <th>Statut</th>
            <?php if ($is_patient): ?><th>Action</th><?php endif; ?>
        </tr></thead>
        <tbody>
        <?php foreach ($rdvs as $r): ?>
        <tr>
            <td><?= date('d/m/Y', strtotime($r['date'])) ?></td>
            <td><?= substr($r['heure_debut'],0,5) ?> - <?= substr($r['heure_fin'],0,5) ?></td>
            <td><?= $is_patient ? 'Dr. '.htmlspecialchars($r['medecin_prenom'].' '.$r['medecin_nom']) : htmlspecialchars($r['patient_prenom'].' '.$r['patient_nom']) ?></td>
            <?php if ($is_patient): ?><td><?= htmlspecialchars($r['specialite']) ?></td><?php endif; ?>
            <td><?php if ($r['statut']==='confirme'): ?><span class="badge-confirme">Confirmé</span><?php elseif ($r['statut']==='annule'): ?><span class="badge-annule">Annulé</span><?php else: ?><span>En attente</span><?php endif; ?></td>
            <?php if ($is_patient && $r['statut'] !== 'annule'): ?>
            <td><form method="POST" onsubmit="return confirm('Annuler ?')">
                <input type="hidden" name="rdv_id" value="<?= $r['id'] ?>">
                <input type="hidden" name="creneau_id" value="<?= $r['creneau_id'] ?>">
                <button type="submit" class="btn-annuler">Annuler</button>
            </form></td>
            <?php elseif ($is_patient): ?><td>-</td><?php endif; ?>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <?php endif; ?>
</div>
</body></html>