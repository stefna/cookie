<?php declare(strict_types=1);

namespace Stefna\Cookie;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

interface CookieManager
{
	public function getCookieJar(ServerRequestInterface $request): CookieJar;

	public function compileCookieJar(CookieJar $cookieJar, ResponseInterface $response): ResponseInterface;
}
