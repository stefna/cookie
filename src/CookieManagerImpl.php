<?php declare(strict_types=1);

namespace Stefna\Cookie;

use DateTimeInterface;
use Psr\Clock\ClockInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final class CookieManagerImpl implements CookieManager
{
	public function __construct(
		private readonly ClockInterface $clock,
	) {}

	public function getCookieJar(ServerRequestInterface $request): CookieJar
	{
		$cookies = [];
		foreach ($request->getCookieParams() as $name => $value) {
			$cookies[] = new ReadCookie($name, $value);
		}
		return new ArrayCookieJar(...$cookies);
	}

	public function compileCookieJar(CookieJar $cookieJar, ResponseInterface $response): ResponseInterface
	{
		foreach ($cookieJar as $cookie) {
			if ($cookie instanceof Cookie) {
				$response = $this->compileCookie($cookie, $response);
			}
		}
		return $response;
	}

	private function compileCookie(Cookie $cookie, ResponseInterface $response): ResponseInterface
	{
		$cookieParts = [
			$cookie->name . '=' . rawurlencode($cookie->value),
		];

		if ($cookie->expires) {
			$now = $this->clock->now();
			$maxAge = $this->clock->now()->add($cookie->expires);
			$cookieParts[] = 'Expires=' . $now->add($cookie->expires)->format(DateTimeInterface::RFC7231);
			$cookieParts[] = 'Max-Age=' . ($maxAge->getTimestamp() - $now->getTimestamp());
		}

		if ($cookie->domain !== null) {
			$cookieParts[] = 'Domain=' . $cookie->domain;
		}

		if ($cookie->path !== null) {
			$cookieParts[] = 'Path=' . $cookie->path;
		}

		if ($cookie->secure) {
			$cookieParts[] = 'Secure';
		}

		if ($cookie->httpOnly) {
			$cookieParts[] = 'HttpOnly';
		}

		if ($cookie->sameSite !== null) {
			$cookieParts[] = 'SameSite=' . $cookie->sameSite->name;
		}

		return $response->withAddedHeader('set-cookie', implode('; ', $cookieParts));
	}
}
