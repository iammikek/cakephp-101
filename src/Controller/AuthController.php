<?php
declare(strict_types=1);

namespace App\Controller;

use App\Exception\ApiException;
use App\Service\JwtService;
use App\Utility\Validator;

class AuthController extends AppController
{
    public function register(): \Cake\Http\Response
    {
        $payload = $this->payload();
        $error = Validator::firstError($payload, [
            'email' => ['required', 'email', 'min:5', 'max:255'],
            'password' => ['required', 'string', 'min:8', 'max:128'],
        ]);
        if ($error !== null) {
            throw new ApiException($error, 422);
        }

        $user = $this->fetchTable('Users')->register((string)$payload['email'], (string)$payload['password']);

        return $this->json([
            'id' => (int)$user->id,
            'email' => $user->email,
        ], 201);
    }

    public function login(): \Cake\Http\Response
    {
        $payload = $this->payload();
        $email = (string)($payload['username'] ?? $payload['email'] ?? '');
        $password = (string)($payload['password'] ?? '');

        if ($email === '' || $password === '') {
            $email = (string)$this->request->getQuery('username', $email);
            $password = (string)$this->request->getQuery('password', $password);
        }

        $user = $this->fetchTable('Users')->authenticate($email, $password);
        if ($user === null) {
            return $this->json(['detail' => 'Incorrect email or password'], 401)
                ->withHeader('WWW-Authenticate', 'Bearer');
        }

        $token = (new JwtService())->createToken((int)$user->id, (string)$user->email);

        return $this->json([
            'access_token' => $token,
            'token_type' => 'bearer',
        ]);
    }

    public function me(): \Cake\Http\Response
    {
        $user = $this->request->getAttribute('identity');

        return $this->json([
            'id' => (int)$user->id,
            'email' => $user->email,
        ]);
    }
}
