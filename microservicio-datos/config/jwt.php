<?php
/**
 * Configuración JWT - Microservicio Datos
 * Sistema de Microservicios - Arquitecturas 2025
 */

class JWTConfig {
    private $secret_key = 'clave_secreta_microservicios_usuarios_2025';
    private $algorithm = 'HS256';
    private $expiration_time = 3600; // 1 hora

    public function getSecretKey() {
        return $this->secret_key;
    }

    public function getAlgorithm() {
        return $this->algorithm;
    }

    public function getExpirationTime() {
        return $this->expiration_time;
    }

    public function generateToken($payload) {
        $header = json_encode(['typ' => 'JWT', 'alg' => $this->algorithm]);
        $payload['exp'] = time() + $this->expiration_time;
        $payload['iat'] = time();
        
        $base64Header = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($header));
        $base64Payload = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode(json_encode($payload)));
        
        $signature = hash_hmac('sha256', $base64Header . "." . $base64Payload, $this->secret_key, true);
        $base64Signature = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($signature));
        
        return $base64Header . "." . $base64Payload . "." . $base64Signature;
    }

    public function validateToken($token) {
        $parts = explode('.', $token);
        if (count($parts) !== 3) {
            return false;
        }

        $header = $parts[0];
        $payload = $parts[1];
        $signature = $parts[2];

        $expectedSignature = hash_hmac('sha256', $header . "." . $payload, $this->secret_key, true);
        $expectedSignature = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($expectedSignature));

        if ($signature !== $expectedSignature) {
            return false;
        }

        $payloadData = json_decode(base64_decode(str_replace(['-', '_'], ['+', '/'], $payload)), true);
        
        if ($payloadData['exp'] < time()) {
            return false;
        }

        return $payloadData;
    }
}
?>
