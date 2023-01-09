<?php declare(strict_types=1);

namespace Stefna\Cookie\Tests;

use PHPUnit\Framework\TestCase;
use Stefna\Cookie\Cookie;
use Stefna\Cookie\ReadCookie;
use Stefna\Cookie\SameSite;

final class ReadCookieTest extends AbstractCookieTest
{
	protected function createCookie(string $name, string $values): ReadCookie|Cookie
	{
		return new ReadCookie($name, $values);
	}
}
