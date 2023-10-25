<?php declare(strict_types=1);

namespace Stefna\Cookie\Tests;

use Nyholm\Psr7\ServerRequest;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Stefna\Cookie\Cookie;
use Stefna\Cookie\CookieManager;
use Stefna\Cookie\HttpCookieManager;
use Stefna\Cookie\SignedCookieManager;
use Sunkan\Dictus\FrozenClock;

final class SignedCookieManagerTest extends TestCase
{
	private string $key = 'test-key';
	private string $cookieName = 'test-name';

	/**
	 * @return array<string, array{string, string}>
	 */
	public static function signDataProvider(): array
	{
		return [
			'empty-value' => ['', '7d5bfb5d63271103341c35f8dc0d055cbdfc7ce16243ce1755ab1ae2017722e2fb3a9e2762e00182e038a25d845f87327d5bfb5d63271103341c35f8dc0d055c'],
			'string-value' => ['value', '7d5bfb5d63271103341c35f8dc0d055cb5a8d6cca96948de284ab0c5a232f78da9cd7a5c5a03ee3d890f03d5654b8e257d5bfb5d63271103341c35f8dc0d055cvalue'],
			'number-value' => ['1234567890', '7d5bfb5d63271103341c35f8dc0d055c42af98d35c98a1d388b74704e6d1c41792f8d6c69ebb3afffa820411571732a87d5bfb5d63271103341c35f8dc0d055c1234567890'],
			'json-value' => ['{"bool":true,"int":123}', '7d5bfb5d63271103341c35f8dc0d055cbdb8d73b4f3309662999ce02711a3adbca1b3069cc23b90340fc766faca6b0147d5bfb5d63271103341c35f8dc0d055c%7B%22bool%22%3Atrue%2C%22int%22%3A123%7D'],
		];
	}

	#[DataProvider('signDataProvider')]
	public function testSign(string $value, string $expected): void
	{
		$manager = $this->getManager();
		$jar = $manager->getCookieJar(new ServerRequest('GET', '/'));
		$cookie = new Cookie($this->cookieName, $value);

		$jar->set($cookie);

		$response = new ResponseStub(function ($name, $value) use ($expected) {
			$this->assertSame('set-cookie', $name);
			$this->assertStringContainsString($expected, $value);
		});

		$manager->compileCookieJar($jar, $response);
	}

	/**
	 * @dataProvider signDataProvider
	 */
	public function testGetCookieJar(string $expectedValue, string $value): void
	{
		$manager = $this->getManager();
		$jar = $manager->getCookieJar((new ServerRequest('GET', '/'))->withCookieParams([
			'test-name' => $value,
		]));

		$this->assertSame($expectedValue, $jar->getString('test-name'));
	}

	public function testGetCookieJarThrowExceptionForInvalidSignedValue(): void
	{
		$manager = $this->getManager();

		$this->expectException(\RuntimeException::class);

		$manager->getCookieJar((new ServerRequest('GET', '/'))->withCookieParams([
			'test-name' => '7d5bfb5d63271103341c35f8dc0d055csdfvalue',
		]));
	}

	public function getManager(): CookieManager
	{
		return new SignedCookieManager(
			new HttpCookieManager(FrozenClock::fromString('2022-12-13 12:00:00')),
			$this->key,
		);
	}
}
