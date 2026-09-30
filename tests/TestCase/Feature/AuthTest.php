<?php
declare(strict_types=1);

namespace App\Test\TestCase\Feature;

class AuthTest extends ApiTestCase
{
    public function testRegisterUser(): void
    {
        $this->postJson('/auth/register', [
            'email' => 'alice@example.com',
            'password' => 'password123',
        ]);
        $this->assertResponseCode(201);
        $this->assertJsonPath('email', 'alice@example.com');
        $this->assertArrayNotHasKey('password', $this->json());
    }

    public function testRegisterDuplicateEmail(): void
    {
        $payload = ['email' => 'test@example.com', 'password' => 'secret123'];
        $this->postJson('/auth/register', $payload);
        $this->assertResponseCode(201);

        $this->postJson('/auth/register', $payload);
        $this->assertResponseCode(409);
        $this->assertJsonPath('code', 'USER_EMAIL_EXISTS');
    }

    public function testLoginSuccess(): void
    {
        $this->postJson('/auth/register', [
            'email' => 'test@example.com',
            'password' => 'secret123',
        ]);
        $this->assertResponseCode(201);

        $this->post('/auth/login', [
            'username' => 'test@example.com',
            'password' => 'secret123',
        ]);
        $this->assertResponseOk();
        $this->assertJsonPath('token_type', 'bearer');
        $this->assertNotEmpty($this->json()['access_token']);
    }

    public function testLoginInvalidPassword(): void
    {
        $this->postJson('/auth/register', [
            'email' => 'test@example.com',
            'password' => 'secret123',
        ]);
        $this->assertResponseCode(201);

        $this->post('/auth/login', [
            'username' => 'test@example.com',
            'password' => 'wrong-password',
        ]);
        $this->assertResponseCode(401);
        $this->assertJsonPath('detail', 'Incorrect email or password');
    }

    public function testReadCurrentUser(): void
    {
        $token = $this->createAuthenticatedToken();
        $this->withHeaders($this->bearerHeaders($token))->getJson('/auth/me');
        $this->assertResponseOk();
        $this->assertJsonPath('email', 'test@example.com');
    }

    public function testReadCurrentUserWithoutToken(): void
    {
        $this->getJson('/auth/me');
        $this->assertResponseCode(401);
    }
}
