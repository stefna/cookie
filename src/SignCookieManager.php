<?php declare(strict_types=1);

namespace Stefna\Cookie;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final class SignCookieManager implements CookieManager
{
	public function __construct(
		private readonly CookieManager $cookieManager,
		#[\SensitiveParameter]
		private readonly string $key,
	) {}

	public function getCookieJar(ServerRequestInterface $request): CookieJar
	{
		$jar = $this->cookieManager->getCookieJar($request);
		$cookies = [];
		foreach ($jar as $cookie) {
			if ($cookie instanceof ReadCookie && $this->isSigned($cookie)) {
				$cookie = $this->validate($cookie);
			}
			$cookies[] = $cookie;
		}

		return new ArrayCookieJar(...$cookies);
	}

	public function compileCookieJar(CookieJar $cookieJar, ResponseInterface $response): ResponseInterface
	{
		foreach ($cookieJar as $cookie) {
			if ($cookie instanceof Cookie && !$this->isSigned($cookie)) {
				$cookieJar->set($this->sign($cookie, $this->key));
			}
		}
		return $this->cookieManager->compileCookieJar($cookieJar, $response);
	}

	private function sign(Cookie $cookie, string $key): Cookie
	{
		$prefix = $this->prefix($cookie);

		$hash = hash_hmac('sha256', $prefix . urlencode($cookie->value), $key);
		if (!$hash) {
			throw new \RuntimeException("Failed to generate HMAC with hash algorithm: sha256.");
		}

		return $cookie->withValue($prefix . $hash . $prefix .  $cookie->value);
	}

	private function validate(ReadCookie $cookie): ReadCookie
	{
		$test = hash_hmac('sha256', '', '');
		$hashLength = mb_strlen($test, '8bit');
		$value = substr($cookie->value, 32);
		$hash = mb_substr($value, 0, $hashLength, '8bit');
		$pureData = mb_substr($value, $hashLength);

		$calculatedHash = hash_hmac('sha256', $pureData, $this->key);

		if (hash_equals($hash, $calculatedHash)) {
			return new ReadCookie($cookie->name, urldecode(mb_substr($pureData, 32)));
		}
		throw new \RuntimeException("The \"{$cookie->name}\" cookie value was tampered with.");
	}

	private function isSigned(ReadCookie|Cookie $cookie): bool
	{
		return strlen($cookie->value) > 32 && str_starts_with($cookie->value, $this->prefix($cookie));
	}

	private function prefix(ReadCookie|Cookie $cookie): string
	{
		return md5(self::class . $cookie->name);
	}
}
