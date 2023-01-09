<?php declare(strict_types=1);

namespace Stefna\Cookie\Tests;

use Nyholm\Psr7\ServerRequest;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Stefna\Cookie\ArrayCookieJar;
use Stefna\Cookie\Cookie;
use Stefna\Cookie\CookieManager;
use Stefna\Cookie\CookieManagerImpl;
use Stefna\Cookie\SameSite;
use Sunkan\Dictus\FrozenClock;

final class ManagerTest extends TestCase
{
	public function testGetCookieJar(): void
	{
		$manager = $this->getManager();

		$jar = $manager->getCookieJar(new ServerRequest('GET', '/'));

		$this->assertCount(0, $jar->getIterator());
	}

	public function testGetCookieHarWithCookies(): void
	{
		$manager = $this->getManager();

		$request = new ServerRequest('GET', '/');
		$request = $request->withCookieParams([
			'numberKey' => '1',
			'boolKey' => 'on',
			'stringKey' => 'random',
		]);

		$jar = $manager->getCookieJar($request);
		$this->assertCount(3, $jar->getIterator());
	}

	/**
	 * @param array<array{0:Cookie,1:string}> $cookies
	 * @dataProvider cookieProvider
	 */
	public function testCompileCookieJarSimple(array $cookies): void
	{
		$manager = $this->getManager();

		$jar = new ArrayCookieJar();

		foreach ($cookies as $cookie) {
			$jar->set($cookie[0]);
		}


		$response = new ResponseStub(function ($name, $value, $count) use ($cookies) {
			$this->assertSame('set-cookie', $name);
			$this->assertSame($cookies[$count][1], $value);
			var_dump($name, $value, $count);
		});

		$manager->compileCookieJar($jar, $response);
	}

	/**
	 * @return CookieManager
	 */
	public function getManager(): CookieManager
	{
		return new CookieManagerImpl(FrozenClock::fromString('2022-12-13 12:00:00'));
	}

	/**
	 * @return array<string, array<array<array{0:Cookie,1:string}>>>
	 */
	public function cookieProvider(): array
	{
		return [
			'cookie with default values' => [
				[
					[
						new Cookie('test', '1'),
						'test=1; Path=/; Secure; HttpOnly; SameSite=Lax',
					],
				],
			],
			'multiple cookies' => [
				[
					[
						new Cookie('test', 'random', httpOnly: false),
						'test=random; Path=/; Secure; SameSite=Lax',
					],
					[
						new Cookie('testExpire', '42', expires: new \DateInterval('P1DT4H')),
						'testExpire=42; Expires=Wed, 14 Dec 2022 16:00:00 GMT; Max-Age=100800; Path=/; Secure; HttpOnly; SameSite=Lax',
					],
				],
			],
			'not secure cookie' => [
				[
					[
						new Cookie('test', '1', secure: false),
						'test=1; Path=/; HttpOnly; SameSite=Lax',
					],
				],
			],
			'with custom domain' => [
				[
					[
						new Cookie('test', '1', domain: '.example.com'),
						'test=1; Domain=.example.com; Path=/; Secure; HttpOnly; SameSite=Lax',
					],
				],
			],
			'without path' => [
				[
					[
						new Cookie('test', '1', path: null),
						'test=1; Secure; HttpOnly; SameSite=Lax',
					],
				],
			],
			'same-site' => [
				[
					[
						new Cookie('testStrict', '1', secure: false, sameSite: SameSite::Strict),
						'testStrict=1; Path=/; HttpOnly; SameSite=Strict',
					],
					[
						new Cookie('testNone', '1', secure: false, sameSite: SameSite::None),
						'testNone=1; Path=/; HttpOnly; SameSite=None',
					],
					[
						new Cookie('test', '1', secure: false, sameSite: null),
						'test=1; Path=/; HttpOnly',
					],
				],
			],
		];
	}
}
