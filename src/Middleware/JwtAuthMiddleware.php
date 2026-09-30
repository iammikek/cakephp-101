<?php
declare(strict_types=1);

namespace App\Middleware;

use App\Service\JwtService;
use Cake\Http\Response;
use Cake\ORM\Locator\LocatorAwareTrait;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class JwtAuthMiddleware implements MiddlewareInterface
{
    use LocatorAwareTrait;

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $header = $request->getHeaderLine('Authorization');
        if (!str_starts_with($header, 'Bearer ')) {
            return $this->unauthorized();
        }

        $claims = (new JwtService())->decodeToken(substr($header, 7));
        if ($claims === null) {
            return $this->unauthorized();
        }

        /** @var \App\Model\Table\UsersTable $users */
        $users = $this->fetchTable('Users');
        $user = $users->find()->where(['Users.id' => $claims['sub']])->first();
        if ($user === null) {
            return $this->unauthorized();
        }

        return $handler->handle($request->withAttribute('identity', $user));
    }

    private function unauthorized(): Response
    {
        $body = json_encode(['detail' => 'Unauthorized'], JSON_THROW_ON_ERROR);

        return (new Response())
            ->withStatus(401)
            ->withType('application/json')
            ->withStringBody($body);
    }
}
