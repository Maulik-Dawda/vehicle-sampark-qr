<?php
// app/Models/AdminModel.php - Handles Database Queries for Admin Authentication & Profile Settings

class AdminModel {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function getAdminById($id) {
        $stmt = $this->pdo->prepare("SELECT * FROM admins WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function getAdminByUsername($username) {
        $stmt = $this->pdo->prepare("SELECT * FROM admins WHERE username = ? OR email = ?");
        $stmt->execute([$username, $username]);
        return $stmt->fetch();
    }

    public function updateProfile($id, $username, $email) {
        $stmt = $this->pdo->prepare("UPDATE admins SET username = ?, email = ? WHERE id = ?");
        return $stmt->execute([$username, $email, $id]);
    }

    public function updatePassword($id, $newHashedPassword) {
        $stmt = $this->pdo->prepare("UPDATE admins SET password = ? WHERE id = ?");
        return $stmt->execute([$newHashedPassword, $id]);
    }

    public function setResetToken($email, $token, $expiresAt) {
        $stmt = $this->pdo->prepare("UPDATE admins SET reset_token = ?, reset_expires_at = ? WHERE email = ?");
        return $stmt->execute([$token, $expiresAt, $email]);
    }

    public function getByResetToken($token) {
        $stmt = $this->pdo->prepare("SELECT * FROM admins WHERE reset_token = ? AND reset_expires_at > CURRENT_TIMESTAMP");
        $stmt->execute([$token]);
        return $stmt->fetch();
    }
}
