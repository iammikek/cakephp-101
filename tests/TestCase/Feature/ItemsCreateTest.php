<?php
declare(strict_types=1);

namespace App\Test\TestCase\Feature;

class ItemsCreateTest extends ApiTestCase
{
    public function testCreateItem(): void
    {
        $token = $this->createAuthenticatedToken();
        $this->withHeaders($this->bearerHeaders($token))->postJson('/items', [
            'name' => 'Widget',
            'description' => 'A nice widget',
            'price' => 9.99,
        ]);
        $this->assertResponseCode(201);
        $this->assertJsonPath('name', 'Widget');
        $this->assertJsonPath('description', 'A nice widget');
        $this->assertJsonPath('price', 9.99);
        $this->assertGreaterThanOrEqual(1, $this->json()['id']);
    }

    public function testCreateItemWithoutAuth(): void
    {
        $this->postJson('/items', [
            'name' => 'Widget',
            'price' => 9.99,
        ]);
        $this->assertResponseCode(401);
    }

    public function testGetItemNotFound(): void
    {
        $this->getJson('/items/99');
        $this->assertResponseCode(404);
        $this->assertJsonPath('code', 'ITEM_NOT_FOUND');
    }

    public function testDeleteItem(): void
    {
        $token = $this->createAuthenticatedToken();
        $headers = $this->bearerHeaders($token);
        $this->withHeaders($headers)->postJson('/items', ['name' => 'To Delete', 'price' => 1.0]);
        $itemId = $this->json()['id'];

        $this->withHeaders($headers)->deleteJson('/items/' . $itemId);
        $this->assertResponseCode(204);

        $this->getJson('/items/' . $itemId);
        $this->assertResponseCode(404);
    }
}
