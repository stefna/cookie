<?php declare(strict_types=1);

namespace Stefna\Cookie;

use DateInterval;
use InvalidArgumentException;

use function preg_match;
use function sprintf;

final readonly class Cookie
{
	public function __construct(
		public string $name,
		public string $value = '',
		public DateInterval|null $expires = null,
		public ?string $domain = null,
		public ?string $path = '/',
		public bool $secure = true,
		public bool $httpOnly = true,
		public ?SameSite $sameSite = SameSite::Lax,
	) {
		$this->assertName($this->name);
		$this->assertPath($this->path);
	}

	public function expire(): Cookie
	{
		if ($this->isExpired()) {
			return $this;
		}
		$expire = new DateInterval('P1Y');
		$expire->invert = 1;

		return $this->with(expires: $expire);
	}

	public function withValue(float|bool|int|string $value): Cookie
	{
		if ($value === $this->value) {
			return $this;
		}
		return $this->with(value: (string)$value);
	}

	public function withExpires(DateInterval|null $expire = null): Cookie
	{
		if ($expire === $this->expires) {
			return $this;
		}

		return $this->with(expires: $expire);
	}

	public function withDomain(?string $domain): Cookie
	{
		if ($domain === $this->domain) {
			return $this;
		}

		return $this->with(domain: $domain);
	}

	public function withPath(?string $path): Cookie
	{
		if ($path === $this->path) {
			return $this;
		}
		$this->assertPath($path);
		return $this->with(path: $path);
	}

	public function withSecure(bool $secure = true): Cookie
	{
		if ($secure === $this->secure) {
			return $this;
		}

		return $this->with(secure: $secure);
	}

	public function withHttpOnly(bool $httpOnly = true): Cookie
	{
		if ($httpOnly === $this->httpOnly) {
			return $this;
		}

		return $this->with(httpOnly: $httpOnly);
	}

	public function withSameSite(?SameSite $sameSite): Cookie
	{
		if ($sameSite === $this->sameSite) {
			return $this;
		}

		return $this->with(sameSite: $sameSite);
	}

	public function with(mixed ...$args): self
	{
		foreach (['name', 'value', 'expires', 'domain', 'path', 'secure', 'httpOnly', 'sameSite'] as $prop) {
			if (!array_key_exists($prop, $args)) {
				$args[$prop] = $this->{$prop};
			}
		}

		return new self(...$args);
	}

	public function isSession(): bool
	{
		if (!$this->expires) {
			return true;
		}
		return !($this->expires->y ||
			$this->expires->m ||
			$this->expires->d ||
			$this->expires->h ||
			$this->expires->i ||
			$this->expires->s);
	}

	public function isExpired(): bool
	{
		if ($this->isSession()) {
			return false;
		}
		if (!$this->expires) {
			return true;
		}
		return (bool)$this->expires->invert;
	}

	private function assertPath(?string $path): void
	{
		if ($path !== null && preg_match('/[\x00-\x1F\x7F\x3B,\ ]/', $path)) {
			throw new InvalidArgumentException(sprintf(
				"The cookie path \"%s\" contains invalid characters.",
				$path
			));
		}
	}

	private function assertName(string $name): void
	{
		if (empty($name)) {
			throw new InvalidArgumentException('The cookie name cannot be empty.');
		}

		if (!preg_match('/^[a-zA-Z0-9!#$%&\' *+\-.^_`|~]+$/', $name)) {
			throw new InvalidArgumentException(sprintf(
				'The cookie name `%s` contains invalid characters; must only contain US-ASCII'
				. ' characters, except control and separator characters, spaces, or tabs.',
				$name
			));
		}
	}
}
