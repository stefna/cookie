<?php declare(strict_types=1);

namespace Stefna\Cookie\Tests;

use PHPUnit\Framework\TestCase;
use Stefna\Cookie\ArrayCookieJar;
use Stefna\Cookie\Cookie;
use Stefna\Cookie\CookieJar;
use Stefna\Cookie\ReadCookie;
use Stefna\Cookie\SameSite;

final class CookieJarTest extends TestCase
{
	public function testNoCookies(): void
	{
		$jar = new ArrayCookieJar();
		$this->assertCount(0, $jar->getIterator());
	}

	public function testReadCookies(): void
	{
		$jar = $this->getJar(
			new ReadCookie('test', 'on'),
			new ReadCookie('testInt', '42'),
		);

		$this->assertCount(2, $jar->getIterator());
		$this->assertTrue($jar->has('test'));
		$this->assertTrue($jar->getBool('test'));
		$this->assertIsInt($jar->getInt('testInt'));
	}

	public function testUpdateCookie(): void
	{
		$jar = $this->getJar(
			new ReadCookie('test', 'on'),
		);

		$cookie = $jar->get('test');
		$this->assertTrue($jar->getBool('test'));
		$this->assertInstanceOf(ReadCookie::class, $cookie);

		$jar->set($cookie->withValue(false));
		$this->assertFalse($jar->getBool('test'));
		$this->assertInstanceOf(Cookie::class, $jar->get('test'));
	}

	public function testRemoveCookieByKey(): void
	{
		$jar = $this->getJar(
			new ReadCookie('test', 'on'),
			new ReadCookie('testRemove', '1'),
		);

		$this->assertTrue($jar->has('testRemove'));
		$jar->remove('testRemove');

		$cookie = $jar->get('testRemove');
		$this->assertInstanceOf(Cookie::class, $cookie);
		$this->assertFalse($cookie->isSession());
		$this->assertTrue($cookie->isExpired());
	}

	public function testRemoveCookieByObject(): void
	{
		$jar = $this->getJar(
			new ReadCookie('test', 'on'),
			new Cookie('testRemove', '1', expires: new \DateInterval('P1D')),
		);

		$this->assertTrue($jar->has('testRemove'));
		/** @var Cookie $cookie */
		$cookie = $jar->get('testRemove');
		$this->assertNotNull($cookie);
		$this->assertFalse($cookie->isExpired());
		$jar->remove($cookie);

		$this->assertNotNull($cookie);
		$removedCookie = $jar->get('testRemove');
		$this->assertInstanceOf(Cookie::class, $removedCookie);

		$this->assertSame(1, $removedCookie->expires?->y);
		$this->assertSame(0, $removedCookie->expires->d);
		$this->assertSame(1, $removedCookie->expires->invert);
		$this->assertFalse($removedCookie->isSession());
		$this->assertTrue($removedCookie->isExpired());
	}

	public function testRemoveReadCookieByObject(): void
	{
		$jar = $this->getJar(
			new ReadCookie('test', 'on'),
			new ReadCookie('testRemove', '1'),
		);

		$this->assertTrue($jar->has('testRemove'));
		$cookie = $jar->get('testRemove');
		$this->assertNotNull($cookie);
		$jar->remove($cookie);

		$this->assertNotNull($cookie);
		$removedCookie = $jar->get('testRemove');
		$this->assertInstanceOf(Cookie::class, $removedCookie);

		$this->assertSame(1, $removedCookie->expires?->y);
		$this->assertSame(0, $removedCookie->expires->d);
		$this->assertSame(1, $removedCookie->expires->invert);
		$this->assertFalse($removedCookie->isSession());
		$this->assertTrue($removedCookie->isExpired());
	}

	public function testRemoveCookieNotFound(): void
	{
		$cookies = [
			new ReadCookie('test', 'on'),
			new Cookie('testRemove', '1', expires: new \DateInterval('P1D')),
		];
		$jar = $this->getJar(...$cookies);

		$jar->remove('not-found');

		$this->assertSame($cookies, array_values(iterator_to_array($jar->getIterator())));
	}

	private function getJar(Cookie|ReadCookie ...$cookies): CookieJar
	{
		return new ArrayCookieJar(...$cookies);
	}
}
