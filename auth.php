<?php
session_start();
include 'config.php';

class Auth {
    private $conexion;
    
    public function __construct($conexion) {
        $this->conexion = $conexion;
    }
    
    public function login($username, $password) {
        $stmt = $this->conexion->prepare("SELECT id, username, password_hash, nombre_completo FROM usuarios WHERE (username = ? OR email = ?) AND activo = 1");
        $stmt->bind_param('ss', $username, $username);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($user = $result->fetch_assoc()) {
            if (password_verify($password, $user['password_hash'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['nombre_completo'] = $user['nombre_completo'];
                
                // Actualizar último login
                $updateStmt = $this->conexion->prepare("UPDATE usuarios SET ultimo_login = NOW() WHERE id = ?");
                $updateStmt->bind_param('i', $user['id']);
                $updateStmt->execute();
                
                return true;
            }
        }
        return false;
    }
    
    public function logout() {
        session_destroy();
        header('Location: login.php');
        exit;
    }
    
    public function isLoggedIn() {
        return isset($_SESSION['user_id']);
    }
    
    public function requireLogin() {
        if (!$this->isLoggedIn()) {
            header('Location: login.php');
            exit;
        }
    }
    
    public function generatePasswordResetToken($email) {
        $token = bin2hex(random_bytes(32));
        $expiry = date('Y-m-d H:i:s', strtotime('+1 hour'));
        
        $stmt = $this->conexion->prepare("UPDATE usuarios SET token_reset = ?, token_reset_expira = ? WHERE email = ? AND activo = 1");
        $stmt->bind_param('sss', $token, $expiry, $email);
        
        if ($stmt->execute() && $stmt->affected_rows > 0) {
            return $token;
        }
        return false;
    }
    
    public function resetPassword($token, $newPassword) {
        $stmt = $this->conexion->prepare("SELECT id FROM usuarios WHERE token_reset = ? AND token_reset_expira > NOW() AND activo = 1");
        $stmt->bind_param('s', $token);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($user = $result->fetch_assoc()) {
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            $updateStmt = $this->conexion->prepare("UPDATE usuarios SET password_hash = ?, token_reset = NULL, token_reset_expira = NULL WHERE id = ?");
            $updateStmt->bind_param('si', $hashedPassword, $user['id']);
            return $updateStmt->execute();
        }
        return false;
    }
}

$auth = new Auth($conexion);
?>
