<?php
declare(strict_types=1);

namespace App\Test\TestCase\Feature;

class ItemsListTest extends ApiTestCase
{
    public function testListItemsEmpty(): void
    {
        $this->getJson('/items');
        $this->assertResponseOk();
        $this->assertSame([
            'items' => [],
            'total' => 0,
            'skip' => 0,
            'limit' => 10,
        ], $this->json());
    }

    public function testListItemsWithPagination(): void
    {
        $token = $this->createAuthenticatedToken();
        $headers = $this->bearerHeaders($token);

        foreach ([['A', 1.0], ['B', 2.0], ['C', 3.0]] as [$name, $price]) {
            $this->withHeaders($headers)->postJson('/items', ['name' => $name, 'price' => $price]);
            $this->assertResponseCode(201);
        }

        $this->getJson('/items?skip=1&limit=2');
        $this->assertResponseOk();
        $this->assertJsonPath('total', 3);
        $this->assertJsonPath('skip', 1);
        $this->assertJsonPath('limit', 2);
        $this->assertCount(2, $this->json()['items']);
        $this->assertJsonPath('items.0.name', 'B');
        $this->assertJsonPath('items.1.name', 'C');
    }

    public function testListItemsValidationErrors(): void
    {
        $this->getJson('/items?limit=101');
        $this->assertResponseCode(422);
    }
}
