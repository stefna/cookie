<?php declare(strict_types=1);

namespace Stefna\Cookie;

use DateInterval;
use InvalidArgumentException;

use function preg_match;
use function sprintf;

final readonly class ReadCookie
{
	public function __construct(
		public string $name,
		public string $value = '',
	) {}

	public function expire(): Cookie
	{
		$expire = new DateInterval('P1Y');
		$expire->invert = 1;
		return $this->with(expires: $expire);
	}

	public function withValue(float|bool|int|string $value): Cookie
	{
		return $this->with(value: (string)$value);
	}

	public function withExpires(DateInterval|null $expire = null): Cookie
	{
		return $this->with(expires: $expire);
	}

	public function withDomain(?string $domain): Cookie
	{
		return $this->with(domain: $domain);
	}

	public function withPath(?string $path): Cookie
	{
		return $this->with(path: $path);
	}

	public function withSecure(bool $secure = true): Cookie
	{
		return $this->with(secure: $secure);
	}

	public function withHttpOnly(bool $httpOnly = true): Cookie
	{
		return $this->with(httpOnly: $httpOnly);
	}

	public function withSameSite(?SameSite $sameSite): Cookie
	{
		return $this->with(sameSite: $sameSite);
	}

	public function with(mixed ...$args): Cookie
	{
		$args['name'] = $this->name;
		if (!isset($args['value'])) {
			$args['value'] = $this->value;
		}

		// @phpstan-ignore-next-line
		return new Cookie(...$args);
	}
}
