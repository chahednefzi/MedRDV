<?php
require_once 'includes/db.php';

if (isset($_SESSION['user_id'])) {
    if ($_SESSION['role'] === 'medecin') {
        header('Location: dashboard/medecin.php');
    } else {
        header('Location: dashboard/patient.php');
    }
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['mot_de_passe'];

    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['mot_de_passe'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['nom'] = $user['nom'];
        $_SESSION['prenom'] = $user['prenom'];

        if ($user['role'] === 'medecin') {
            header('Location: dashboard/medecin.php');
        } else {
            header('Location: dashboard/patient.php');
        }
        exit;
    } else {
        $error = "Email ou mot de passe incorrect.";
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>MedRDV - Connexion</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; background: #f0f4f8; display: flex; justify-content: center; align-items: center; min-height: 100vh; }
        .card { background: white; padding: 2rem; border-radius: 12px; width: 380px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        h2 { color: #2563eb; margin-bottom: 1.5rem; text-align: center; }
        input { width: 100%; padding: 10px; margin-bottom: 1rem; border: 1px solid #ddd; border-radius: 8px; font-size: 14px; }
        button { width: 100%; padding: 12px; background: #2563eb; color: white; border: none; border-radius: 8px; font-size: 15px; cursor: pointer; }
        button:hover { background: #1d4ed8; }
        .error { background: #fee2e2; color: #dc2626; padding: 10px; border-radius: 8px; margin-bottom: 1rem; font-size: 13px; }
        a { display: block; text-align: center; margin-top: 1rem; color: #2563eb; font-size: 13px; }
    </style>
</head>
<body>
<div class="card">
    <h2>Connexion MedRDV</h2>
    <?php if ($error): ?><div class="error"><?= $error ?></div><?php endif; ?>
    <form method="POST">
        <input type="email" name="email" placeholder="Email" required>
        <input type="password" name="mot_de_passe" placeholder="Mot de passe" required>
        <button type="submit">Se connecter</button>
    </form>
    <a href="register.php">Pas encore de compte ? S'inscrire</a>
</div>
</body>
</html>