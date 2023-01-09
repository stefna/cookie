<?php declare(strict_types=1);

namespace Stefna\Cookie;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class CookieMiddleware implements MiddlewareInterface
{
	public function __construct(
		private readonly CookieManager $cookieManager,
	) {}

	public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
	{
		$jar = $this->cookieManager->getCookieJar($request);
		$response = $handler->handle($request->withAttribute(CookieJar::class, $jar));
		return $this->cookieManager->compileCookieJar($jar, $response);
	}
}
