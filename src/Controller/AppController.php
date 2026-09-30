<?php
declare(strict_types=1);

namespace App\Controller;

use Cake\Controller\Controller;

class AppController extends Controller
{
    public function initialize(): void
    {
        parent::initialize();
        $this->viewBuilder()->disableAutoLayout();
        $this->autoRender = false;
    }

    /**
     * @param array<string, mixed> $data
     */
    protected function json(array $data, int $status = 200): \Cake\Http\Response
    {
        $body = json_encode($data, JSON_THROW_ON_ERROR);

        return $this->response
            ->withStatus($status)
            ->withType('application/json')
            ->withStringBody($body);
    }

    /**
     * @return array<string, mixed>
     */
    protected function payload(): array
    {
        $data = $this->request->getData();

        return is_array($data) ? $data : [];
    }
}
