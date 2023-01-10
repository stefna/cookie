<?php declare(strict_types=1);

namespace Stefna\Cookie\Tests;

use Nyholm\Psr7\ServerRequest;
use PHPUnit\Framework\TestCase;
use Stefna\Cookie\Cookie;
use Stefna\Cookie\CookieManager;
use Stefna\Cookie\HttpCookieManager;
use Stefna\Cookie\SignCookieManager;
use Sunkan\Dictus\FrozenClock;

final class SignCookieManagerTest extends TestCase
{
	private string $key = 'test-key';
	private string $cookieName = 'test-name';

	public function signDataProvider(): array
	{
		return [
			'empty-value' => ['', '6466954f2faa59730dfd554bb7259ee95ef6b766e85107066c6679d54aeeb8e235eb94fa3eea521811e60089a35251bb6466954f2faa59730dfd554bb7259ee9'],
			'string-value' => ['value', '6466954f2faa59730dfd554bb7259ee9cf3867414f896c99e77083e2ebccfbd1397ed0c0e8c242aff993ce458a539afc6466954f2faa59730dfd554bb7259ee9value'],
			'number-value' => ['1234567890', '6466954f2faa59730dfd554bb7259ee9ff06d61e72f6b20f576b8a8a72044dd35a35a3f5413bb4b1c98cd2759ff8fe026466954f2faa59730dfd554bb7259ee91234567890'],
			'json-value' => ['{"bool":true,"int":123}', '6466954f2faa59730dfd554bb7259ee9f76b84bbe6eb3ee5e82ff2bf0786f4cb8f0c781e10849194ed7ac47f3f3394816466954f2faa59730dfd554bb7259ee9%7B%22bool%22%3Atrue%2C%22int%22%3A123%7D'],
		];
	}

	/**
	 * @dataProvider signDataProvider
	 */
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
			'test-name' => '6466954f2faa59730dfd554bb7259ee9cfb7259ee9value',
		]));
	}

	public function getManager(): CookieManager
	{
		return new SignCookieManager(
			new HttpCookieManager(FrozenClock::fromString('2022-12-13 12:00:00')),
			$this->key,
		);
	}
}
