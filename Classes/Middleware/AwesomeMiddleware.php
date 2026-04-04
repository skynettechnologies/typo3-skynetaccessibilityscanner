<?php
namespace Skynettechnologies\Skynetaccessibilityscanner\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class AwesomeMiddleware implements MiddlewareInterface
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        // Handle request normally
        $response = $handler->handle($request);

        // Example: Get domain
        $uri = $request->getUri();
        $domain = $uri->getHost();

        // Return response without modifying HTML
        return $response;
    }
}