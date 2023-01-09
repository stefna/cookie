<?php declare(strict_types=1);

namespace Stefna\Cookie\Tests;

use Nyholm\Psr7\Response;
use Nyholm\Psr7\ServerRequest;
use Nyholm\Psr7\Stream;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Stefna\Cookie\Cookie;
use Stefna\Cookie\CookieJar;
use Stefna\Cookie\CookieManager;
use Stefna\Cookie\CookieManagerImpl;
use Stefna\Cookie\CookieMiddleware;
use Sunkan\Dictus\FrozenClock;

final class CookieMiddlewareTest extends TestCase
{
	public function testProcess(): void
	{
		$manager = $this->getManager();
		$request = $this->createServerRequest([
			'name' => 'value',
		]);
		$middleware = new CookieMiddleware($manager);
		$response = $middleware->process($request, $this->createRequestHandler(function (ServerRequestInterface $r) {
			$jar = $r->getAttribute(CookieJar::class);
			$this->assertTrue($jar->has('name'));
			$jar->set(new Cookie('test', 'random'));
			return $r;
		}));

		$cookieHeaders = $response->getHeader('set-cookie');
		$this->assertSame('test=random; Path=/; Secure; HttpOnly; SameSite=Lax', $cookieHeaders[0]);
	}

	/**
	 * @return CookieManager
	 */
	public function getManager(): CookieManager
	{
		return new CookieManagerImpl(FrozenClock::fromString('2022-12-13 12:00:00'));
	}

	private function createServerRequest(array $cookieParams = []): ServerRequestInterface
	{
		return (new ServerRequest('GET', '/'))->withCookieParams($cookieParams);
	}

	private function createRequestHandler(callable $handler): RequestHandlerInterface
	{
		return new class ($handler) implements RequestHandlerInterface {
			/** @var callable */
			private $handler;

			public function __construct(callable $handler)
			{
				$this->handler = $handler;
			}

			public function handle(ServerRequestInterface $request): ResponseInterface
			{
				$request = ($this->handler)($request);

				$content = '';

				foreach ($request->getCookieParams() as $name => $value) {
					$content .= "{$name}:{$value},";
				}

				$stream = Stream::create(rtrim($content, ','));
				return (new Response())->withBody($stream);
			}
		};
	}
}
