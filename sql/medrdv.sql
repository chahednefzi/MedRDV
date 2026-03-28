-- ============================================
-- MedRDV - Script de création de la base de données
-- Stack : PHP + MySQL (XAMPP)
-- ============================================

CREATE DATABASE IF NOT EXISTS medrdv CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE medrdv;

-- ============================================
-- Table : users (patients + médecins + admin)
-- ============================================
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    prenom VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    mot_de_passe VARCHAR(255) NOT NULL,
    role ENUM('patient', 'medecin', 'admin') NOT NULL DEFAULT 'patient',
    telephone VARCHAR(20),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ============================================
-- Table : patients
-- ============================================
CREATE TABLE patients (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    date_naissance DATE,
    adresse VARCHAR(255),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- ============================================
-- Table : medecins
-- ============================================
CREATE TABLE medecins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    specialite VARCHAR(100) NOT NULL,
    numero_ordre VARCHAR(50),
    cabinet VARCHAR(150),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- ============================================
-- Table : creneaux (disponibilités médecin)
-- ============================================
CREATE TABLE creneaux (
    id INT AUTO_INCREMENT PRIMARY KEY,
    medecin_id INT NOT NULL,
    date DATE NOT NULL,
    heure_debut TIME NOT NULL,
    heure_fin TIME NOT NULL,
    disponible TINYINT(1) DEFAULT 1,
    FOREIGN KEY (medecin_id) REFERENCES medecins(id) ON DELETE CASCADE
);

-- ============================================
-- Table : rendez_vous
-- ============================================
CREATE TABLE rendez_vous (
    id INT AUTO_INCREMENT PRIMARY KEY,
    patient_id INT NOT NULL,
    creneau_id INT NOT NULL,
    statut ENUM('confirme', 'annule', 'en_attente') DEFAULT 'en_attente',
    motif VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (patient_id) REFERENCES patients(id) ON DELETE CASCADE,
    FOREIGN KEY (creneau_id) REFERENCES creneaux(id) ON DELETE CASCADE
);

-- ============================================
-- Données de test
-- ============================================

-- Mot de passe : "password123" hashé avec password_hash()
INSERT INTO users (nom, prenom, email, mot_de_passe, role, telephone) VALUES
('Admin', 'MedRDV', 'admin@medrdv.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', '00000000'),
('Ben Ali', 'Mohamed', 'medecin@medrdv.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'medecin', '22222222'),
('Nefzi', 'Shahed', 'patient@medrdv.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'patient', '55555555');

INSERT INTO medecins (user_id, specialite, numero_ordre, cabinet) VALUES
(2, 'Médecine générale', 'MED-2024-001', 'Cabinet Central Tunis');

INSERT INTO patients (user_id, date_naissance, adresse) VALUES
(3, '2000-01-01', 'Tunis, Tunisie');

INSERT INTO creneaux (medecin_id, date, heure_debut, heure_fin, disponible) VALUES
(1, '2026-03-30', '09:00:00', '09:30:00', 1),
(1, '2026-03-30', '09:30:00', '10:00:00', 1),
(1, '2026-03-30', '10:00:00', '10:30:00', 1),
(1, '2026-03-31', '14:00:00', '14:30:00', 1),
(1, '2026-03-31', '14:30:00', '15:00:00', 1);
