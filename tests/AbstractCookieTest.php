<?php declare(strict_types=1);

namespace Stefna\Cookie\Tests;

use PHPUnit\Framework\TestCase;
use Stefna\Cookie\Cookie;
use Stefna\Cookie\ReadCookie;
use Stefna\Cookie\SameSite;

abstract class AbstractCookieTest extends TestCase
{
	abstract protected function createCookie(string $name, string $values): ReadCookie|Cookie;

	public function testExpireCookie(): void
	{
		$orgCookie = $this->createCookie('test', 'on');

		$cookie2 = $orgCookie->expire();
		$this->assertInstanceOf(Cookie::class, $cookie2);
		$this->assertTrue($cookie2->isExpired());
		$this->assertSame(1, $cookie2->expires?->y);
		$this->assertSame('on', $cookie2->value);
	}

	public function testWithValue(): void
	{
		$orgCookie = $this->createCookie('test', 'on');
		$cookie = $orgCookie->withValue($newValue = 'random');
		$this->assertSame($newValue, $cookie->value);
		$this->assertNotSame($orgCookie, $cookie);
		$this->assertNotSame($orgCookie->value, $cookie->value);
	}

	public function testConstructorThrowExceptionForPassedEmptyName(): void
	{
		$this->expectException(\InvalidArgumentException::class);
		new Cookie('');
	}

	/**
	 * @return array<array{string}>
	 */
	public function invalidNameProvider(): array
	{
		return [
			['"'],
			['@'],
			['('],
			[')'],
			['='],
			['\\'],
			['['],
			[']'],
			[','],
			[';'],
			[':'],
			['<'],
			['>'],
			['?'],
			['/'],
			['{'],
			['}'],
			['name[]'],
			["\x00"],
			["\x1F"],
		];
	}

	/**
	 * @dataProvider invalidNameProvider
	 * @param string $name
	 */
	public function testConstructorThrowExceptionForPassedInvalidName($name): void
	{
		$this->expectException(\InvalidArgumentException::class);
		new Cookie($name);
	}

	public function testWithExpiresTime(): void
	{
		$orgCookie = $this->createCookie('test', 'on');
		$expire = \DateInterval::createFromDateString('+1 hour');
		$cookie = $orgCookie->withExpires($expire);
		$this->assertSame($expire, $cookie->expires);
		$this->assertNotSame($orgCookie, $cookie);

		$newExpire = new \DateInterval('P1D');
		$new = $cookie->withExpires($newExpire);
		$this->assertSame($newExpire, $new->expires);
		$this->assertNotSame($cookie, $new);
	}

	public function testWithExpiresEmpty(): void
	{
		$orgCookie = $this->createCookie('test', 'on');
		$newWithNull = $orgCookie->withExpires(null);
		$this->assertEmpty($newWithNull->expires);
	}

	public function testWithDomain(): void
	{
		$orgCookie = $this->createCookie('test', 'on');
		$cookie = $orgCookie->withDomain($domain = 'example.com');
		$this->assertSame($domain, $cookie->domain);
		$this->assertNotSame($orgCookie, $cookie);

		$new = $cookie->withDomain($newDomain = '.example.com');
		$this->assertSame($newDomain, $new->domain);
		$this->assertNotSame($cookie, $new);
	}

	public function testWithDomainEmpty(): void
	{
		$orgCookie = $this->createCookie('test', 'on');
		$cookie = $orgCookie->withDomain(null);
		$this->assertEmpty($cookie->domain);

		$new = $cookie->withDomain('');
		$this->assertEmpty($new->domain);
	}

	public function testWithPath(): void
	{
		$orgCookie = $this->createCookie('test', 'on');
		$cookie = $orgCookie->withPath($path = '/path');
		$this->assertSame($path, $cookie->path);
		$this->assertNotSame($orgCookie, $cookie);

		$new = $cookie->withPath($newDomain = '/path/to/target');
		$this->assertSame($newDomain, $new->path);
		$this->assertNotSame($cookie, $new);
	}

	public function testWithPathEmpty(): void
	{
		$orgCookie = $this->createCookie('test', 'on');
		$cookie = $orgCookie->withPath(null);
		$this->assertEmpty($cookie->path);

		$new = $cookie->withPath('');
		$this->assertEmpty($new->path);
	}

	/**
	 * @dataProvider invalidPathProvider
	 */
	public function testInvalidPaths(string $path): void
	{
		$orgCookie = $this->createCookie('test', 'on');

		$this->expectException(\InvalidArgumentException::class);

		$orgCookie->withPath($path);
	}

	/**
	 * @return array<string, array{string}>
	 */
	public function invalidPathProvider(): array
	{
		return [
			'path with comma' => ['/path,test'],
			'path with semicolon' => ['/path;test'],
			'path with space' => ['/path test'],
			'path with tab' => ["/path\ttest"],
			'path with newline 1' => ["/path\rtest"],
			'path with newline 2' => ["/path\ntest"],
			'path with 013' => ["/path\013test"],
			'path with 014' => ["/path\014test"],
		];
	}

	public function testWithSecure(): void
	{
		$orgCookie = $this->createCookie('test', 'on');
		$cookie = $orgCookie->withSecure(false);
		$this->assertFalse($cookie->secure);
		$this->assertNotSame($orgCookie, $cookie);

		$new = $cookie->withSecure(true);
		$this->assertTrue($new->secure);
		$this->assertNotSame($cookie, $new);
		$this->assertNotSame($orgCookie, $new);
	}

	public function testWithHttpOnly(): void
	{
		$orgCookie = $this->createCookie('test', 'on');
		$cookie = $orgCookie->withHttpOnly(false);
		$this->assertFalse($cookie->httpOnly);
		$this->assertNotSame($orgCookie, $cookie);

		$new = $cookie->withHttpOnly(true);
		$this->assertTrue($new->httpOnly);
		$this->assertNotSame($cookie, $new);
		$this->assertNotSame($orgCookie, $new);
	}

	public function testWithSameSite(): void
	{
		$orgCookie = $this->createCookie('test', 'on');
		$cookie = $orgCookie->withSameSite(SameSite::Strict);
		$this->assertSame(SameSite::Strict, $cookie->sameSite);
		$this->assertNotSame($orgCookie, $cookie);

		$new = $cookie->withSameSite(SameSite::None);
		$this->assertSame(SameSite::None, $new->sameSite);
		$this->assertNotSame($cookie, $new);
	}

	public function testWithSameSiteEmpty(): void
	{
		$orgCookie = $this->createCookie('test', 'on');
		$cookie = $orgCookie->withSameSite(null);
		$this->assertNull($cookie->sameSite);
	}
}
