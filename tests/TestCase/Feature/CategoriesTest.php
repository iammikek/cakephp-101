<?php
declare(strict_types=1);

namespace App\Test\TestCase\Feature;

class CategoriesTest extends ApiTestCase
{
    public function testCreateCategory(): void
    {
        $token = $this->createAuthenticatedToken();
        $this->withHeaders($this->bearerHeaders($token))->postJson('/categories', [
            'name' => 'Tools',
            'description' => 'Hand tools',
        ]);
        $this->assertResponseCode(201);
        $this->assertGreaterThanOrEqual(1, $this->json()['id']);
        $this->assertJsonPath('name', 'Tools');
        $this->assertJsonPath('description', 'Hand tools');
    }

    public function testCreateCategoryWithoutAuth(): void
    {
        $this->postJson('/categories', ['name' => 'Tools']);
        $this->assertResponseCode(401);
    }

    public function testCreateCategoryDuplicateName(): void
    {
        $token = $this->createAuthenticatedToken();
        $headers = $this->bearerHeaders($token);
        $this->withHeaders($headers)->postJson('/categories', ['name' => 'foo']);
        $this->assertResponseCode(201);

        $this->withHeaders($headers)->postJson('/categories', [
            'name' => 'foo',
            'description' => 'duplicate',
        ]);
        $this->assertResponseCode(409);
        $this->assertJsonPath('code', 'CATEGORY_NAME_EXISTS');
    }

    public function testListCategories(): void
    {
        $token = $this->createAuthenticatedToken();
        $this->withHeaders($this->bearerHeaders($token))->postJson('/categories', ['name' => 'Books']);
        $this->assertResponseCode(201);

        $this->getJson('/categories');
        $this->assertResponseOk();
        $this->assertJsonPath('total', 1);
        $this->assertJsonPath('items.0.name', 'Books');
    }
}
