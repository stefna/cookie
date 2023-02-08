<?php declare(strict_types=1);

namespace Stefna\Cookie\Tests;

use Stefna\Cookie\Cookie;
use Stefna\Cookie\ReadCookie;

final class CookieTest extends AbstractCookieTestCase
{
	public function testExpire(): void
	{
		$orgCookie = new Cookie('test', 'on');
		$this->assertFalse($orgCookie->isExpired());
		$this->assertTrue($orgCookie->isSession());

		$cookie = $orgCookie->expire();
		$this->assertTrue($cookie->isExpired());
		$this->assertFalse($cookie->isSession());
		$this->assertNotSame($orgCookie, $cookie);
	}

	public function testWithDomainIfNotChangedDoNotClone(): void
	{
		$domain = '.domain.is';
		$cookie = new Cookie('test', 'on', domain: $domain);
		$this->assertSame($domain, $cookie->domain);

		$new = $cookie->withDomain($domain);
		$this->assertSame($domain, $new->domain);
		$this->assertSame($cookie, $new);
	}

	public function testWithPathIfNotChangedDoNotClone(): void
	{
		$path = '/path';
		$cookie = new Cookie('test', 'on', path: $path);
		$this->assertSame($path, $cookie->path);

		$new = $cookie->withPath($path);
		$this->assertSame($path, $new->path);
		$this->assertSame($cookie, $new);
	}

	public function testWithSecureIfNotChangedDoNotClone(): void
	{
		$cookie = new Cookie('test', 'on', secure: true);
		$this->assertTrue($cookie->secure);

		$new = $cookie->withSecure(true);
		$this->assertTrue($new->secure);
		$this->assertSame($cookie, $new);
	}

	public function testWithHttpOnlyIfNotChangedDoNotClone(): void
	{
		$cookie = new Cookie('test', 'on', httpOnly: true);
		$this->assertTrue($cookie->httpOnly);

		$new = $cookie->withHttpOnly(true);
		$this->assertTrue($new->httpOnly);
		$this->assertSame($cookie, $new);
	}

	protected function createCookie(string $name, string $values): ReadCookie|Cookie
	{
		return new Cookie($name, $values);
	}
}
