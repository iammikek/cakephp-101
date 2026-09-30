<?php
declare(strict_types=1);

namespace App\Service;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use function Cake\Core\env;

class JwtService
{
    public function createToken(int $userId, string $email): string
    {
        $now = time();

        return JWT::encode([
            'sub' => $userId,
            'email' => $email,
            'iat' => $now,
            'exp' => $now + 3600,
        ], $this->secret(), 'HS256');
    }

    /**
     * @return array{sub: int, email: string}|null
     */
    public function decodeToken(string $token): ?array
    {
        try {
            $payload = JWT::decode($token, new Key($this->secret(), 'HS256'));

            return [
                'sub' => (int)$payload->sub,
                'email' => (string)$payload->email,
            ];
        } catch (\Throwable) {
            return null;
        }
    }

    private function secret(): string
    {
        return (string)env('JWT_SECRET', 'dev-only-cakephp-101-jwt-secret-key');
    }
}
