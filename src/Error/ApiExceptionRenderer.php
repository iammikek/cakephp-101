<?php
declare(strict_types=1);

namespace App\Error;

use App\Exception\ApiException;
use Cake\Error\Renderer\WebExceptionRenderer;
use Cake\Http\Response;
use Psr\Http\Message\ResponseInterface;

class ApiExceptionRenderer extends WebExceptionRenderer
{
    public function render(): ResponseInterface
    {
        $exception = $this->error;
        $payload = ['detail' => $exception->getMessage() !== '' ? $exception->getMessage() : 'Error'];
        $status = $this->getHttpCode($exception);

        if ($exception instanceof ApiException) {
            $status = $exception->status;
            if ($exception->errorCode !== null) {
                $payload['code'] = $exception->errorCode;
            }
        }

        $body = json_encode($payload, JSON_THROW_ON_ERROR);

        return (new Response())
            ->withStatus($status)
            ->withType('application/json')
            ->withStringBody($body);
    }
}
