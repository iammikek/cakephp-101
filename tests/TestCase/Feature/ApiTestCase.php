<?php
declare(strict_types=1);

namespace App\Test\TestCase\Feature;

use Cake\TestSuite\IntegrationTestTrait;
use Cake\TestSuite\TestCase;

abstract class ApiTestCase extends TestCase
{
    use IntegrationTestTrait;

    protected function setUp(): void
    {
        parent::setUp();
        $this->fetchTable('Items')->deleteAll(['id >' => 0]);
        $this->fetchTable('Categories')->deleteAll(['id >' => 0]);
        $this->fetchTable('Users')->deleteAll(['id >' => 0]);
    }

    /**
     * @param array<string, string> $headers
     */
    protected function applyHeaders(array $headers): void
    {
        $existing = $this->_request['headers'] ?? [];
        $this->_request['headers'] = array_merge($existing, $headers);
    }

    /**
     * @param array<string, mixed> $data
     */
    protected function postJson(string $url, array $data): static
    {
        $this->applyHeaders([
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        ]);
        $this->post($url, json_encode($data));

        return $this;
    }

    protected function getJson(string $url): static
    {
        $this->applyHeaders(['Accept' => 'application/json']);
        $this->get($url);

        return $this;
    }

    protected function deleteJson(string $url): static
    {
        $this->applyHeaders([
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        ]);
        $this->delete($url);

        return $this;
    }

    /**
     * @param array<string, string> $headers
     */
    protected function withHeaders(array $headers): static
    {
        $this->applyHeaders($headers);

        return $this;
    }

    /**
     * @return array<string, string>
     */
    protected function bearerHeaders(string $token): array
    {
        return ['Authorization' => 'Bearer ' . $token];
    }

    protected function createAuthenticatedToken(): string
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

        return $this->json()['access_token'];
    }

    /**
     * @return array<string, mixed>
     */
    protected function json(): array
    {
        $decoded = json_decode((string)$this->_response->getBody(), true);

        return is_array($decoded) ? $decoded : [];
    }

    protected function assertJsonPath(string $path, mixed $expected): void
    {
        $data = $this->json();
        foreach (explode('.', $path) as $part) {
            $this->assertIsArray($data);
            $key = ctype_digit($part) ? (int)$part : $part;
            $this->assertArrayHasKey($key, $data);
            $data = $data[$key];
        }
        $this->assertSame($expected, $data);
    }
}
