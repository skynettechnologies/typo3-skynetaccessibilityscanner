<?php
namespace Skynettechnologies\Skynetaccessibilityscanner\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use TYPO3\CMS\Core\Http\JsonResponse;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Http\Response;
use TYPO3\CMS\Core\Http\Stream;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Core\Environment;

class AwesomeMiddleware implements MiddlewareInterface
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $response = $handler->handle($request);
        $script = "<script id='aioa-adawidget' src='https://www.skynettechnologies.com/accessibility/js/all-in-one-accessibility-js-widget-minify.js?aioa_reg_req=true&colorcode=&token=&position=bottom_right' async='true'></script>";

		$html = $response->getBody();
        $html = str_replace("</body>","$script</body>",$html);

        $body = new Stream('php://temp', 'wb+');

        $body->write($html);
        $response = $response->withBody($body);

        return $response;
    }
}
