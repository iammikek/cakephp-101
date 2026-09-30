<?php
declare(strict_types=1);

namespace App\Test\TestCase\Feature;

class HealthTest extends ApiTestCase
{
    public function testRoot(): void
    {
        $this->getJson('/');
        $this->assertResponseOk();
        $this->assertSame(['message' => 'Hello from cakephp-101'], $this->json());
    }

    public function testHealth(): void
    {
        $this->getJson('/health');
        $this->assertResponseOk();
        $this->assertSame(['status' => 'ok'], $this->json());
    }
}
