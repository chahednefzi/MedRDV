<?php
require_once 'includes/db.php';
session_start();

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = trim($_POST['nom']);
    $prenom = trim($_POST['prenom']);
    $email = trim($_POST['email']);
    $password = $_POST['mot_de_passe'];
    $role = $_POST['role'];
    $telephone = trim($_POST['telephone']);

    if (empty($nom) || empty($prenom) || empty($email) || empty($password)) {
        $error = "Tous les champs sont obligatoires.";
    } else {
        // Vérifier si email existe déjà
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        
        if ($stmt->fetch()) {
            $error = "Cet email est déjà utilisé.";
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (nom, prenom, email, mot_de_passe, role, telephone) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$nom, $prenom, $email, $hash, $role, $telephone]);
            $user_id = $pdo->lastInsertId();

            // Créer profil selon le rôle
            if ($role === 'patient') {
                $stmt = $pdo->prepare("INSERT INTO patients (user_id) VALUES (?)");
                $stmt->execute([$user_id]);
            } elseif ($role === 'medecin') {
                $specialite = trim($_POST['specialite'] ?? 'Généraliste');
                $stmt = $pdo->prepare("INSERT INTO medecins (user_id, specialite) VALUES (?, ?)");
                $stmt->execute([$user_id, $specialite]);
            }

            $success = "Compte créé avec succès ! Vous pouvez vous connecter.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>MedRDV - Inscription</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; background: #f0f4f8; display: flex; justify-content: center; align-items: center; min-height: 100vh; }
        .card { background: white; padding: 2rem; border-radius: 12px; width: 400px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        h2 { color: #2563eb; margin-bottom: 1.5rem; text-align: center; }
        input, select { width: 100%; padding: 10px; margin-bottom: 1rem; border: 1px solid #ddd; border-radius: 8px; font-size: 14px; }
        button { width: 100%; padding: 12px; background: #2563eb; color: white; border: none; border-radius: 8px; font-size: 15px; cursor: pointer; }
        button:hover { background: #1d4ed8; }
        .error { background: #fee2e2; color: #dc2626; padding: 10px; border-radius: 8px; margin-bottom: 1rem; font-size: 13px; }
        .success { background: #dcfce7; color: #16a34a; padding: 10px; border-radius: 8px; margin-bottom: 1rem; font-size: 13px; }
        a { display: block; text-align: center; margin-top: 1rem; color: #2563eb; font-size: 13px; }
    </style>
</head>
<body>
<div class="card">
    <h2>Inscription MedRDV</h2>
    <?php if ($error): ?><div class="error"><?= $error ?></div><?php endif; ?>
    <?php if ($success): ?><div class="success"><?= $success ?></div><?php endif; ?>
    <form method="POST">
        <input type="text" name="nom" placeholder="Nom" required>
        <input type="text" name="prenom" placeholder="Prénom" required>
        <input type="email" name="email" placeholder="Email" required>
        <input type="password" name="mot_de_passe" placeholder="Mot de passe" required>
        <input type="tel" name="telephone" placeholder="Téléphone">
        <select name="role">
            <option value="patient">Patient</option>
            <option value="medecin">Médecin</option>
        </select>
        <div id="specialite-div" style="display:none">
            <input type="text" name="specialite" placeholder="Spécialité médicale">
        </div>
        <button type="submit">S'inscrire</button>
    </form>
    <a href="login.php">Déjà un compte ? Se connecter</a>
</div>
<script>
    document.querySelector('select[name="role"]').addEventListener('change', function() {
        document.getElementById('specialite-div').style.display = this.value === 'medecin' ? 'block' : 'none';
    });
</script>
</body>
</html>