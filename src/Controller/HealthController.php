<?php
declare(strict_types=1);

namespace App\Controller;

class HealthController extends AppController
{
    public function index(): \Cake\Http\Response
    {
        return $this->json(['message' => 'Hello from cakephp-101']);
    }

    public function health(): \Cake\Http\Response
    {
        return $this->json(['status' => 'ok']);
    }
}
