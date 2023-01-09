<?php declare(strict_types=1);

namespace Stefna\Cookie;

use Stefna\Collection\ScalarMapTrait;

final class ArrayCookieJar implements CookieJar
{
	use ScalarMapTrait;

	/** @var array<string, Cookie|ReadCookie> */
	private array $data = [];

	public function __construct(Cookie|ReadCookie ...$cookies)
	{
		foreach ($cookies as $cookie) {
			$this->data[$cookie->name] = $cookie;
		}
	}

	public function getRawValue(string $key): mixed
	{
		return isset($this->data[$key]) ? $this->data[$key]->value : null;
	}

	public function get(string $name): null|Cookie|ReadCookie
	{
		return $this->data[$name] ?? null;
	}

	public function has(string $key): bool
	{
		return isset($this->data[$key]);
	}

	public function set(Cookie $cookie): void
	{
		$this->data[$cookie->name] = $cookie;
	}

	public function remove(ReadCookie|Cookie|string $nameOrCookie): void
	{
		if (is_string($nameOrCookie)) {
			$cookie = $this->data[$nameOrCookie] ?? null;
		}
		else {
			$cookie = $nameOrCookie;
		}
		if (!$cookie) {
			return;
		}
		$this->data[$cookie->name] = $cookie->expire();
	}

	/**
	 * @return \ArrayIterator<array-key, Cookie|ReadCookie>
	 */
	public function getIterator(): \ArrayIterator
	{
		return new \ArrayIterator($this->data);
	}
}
