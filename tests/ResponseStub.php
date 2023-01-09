<?php declare(strict_types=1);

namespace Stefna\Cookie\Tests;

use Nyholm\Psr7\Stream;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

final class ResponseStub implements ResponseInterface
{
	/**
	 * @var callable
	 */
	private $headerAssert;

	private int $count = 0;

	public function __construct(
		callable $headerAssert,
	) {
		$this->headerAssert = $headerAssert;
	}

	public function getProtocolVersion()
	{
		return '1.1';
	}

	public function withProtocolVersion($version)
	{
		return $this;
	}

	public function getHeaders()
	{
		return [];
	}

	public function hasHeader($name)
	{
		return true;
	}

	public function getHeader($name)
	{
		return [];
	}

	public function getHeaderLine($name)
	{
		return '';
	}

	public function withHeader($name, $value)
	{
		return $this;
	}

	public function withAddedHeader($name, $value)
	{
		$assert = $this->headerAssert;
		$assert($name, $value, $this->count);
		$this->count++;
		return $this;
	}

	public function withoutHeader($name)
	{
		return $this;
	}

	public function getBody()
	{
		return Stream::create('');
	}

	public function withBody(StreamInterface $body)
	{
		return $this;
	}

	public function getStatusCode()
	{
		return 200;
	}

	public function withStatus($code, $reasonPhrase = '')
	{
		return $this;
	}

	public function getReasonPhrase()
	{
		return '';
	}
}
