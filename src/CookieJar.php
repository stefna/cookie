<?php declare(strict_types=1);

namespace Stefna\Cookie;

use Stefna\Collection\ScalarMap;

/**
 * @extends \IteratorAggregate<array-key, Cookie|ReadCookie>
 */
interface CookieJar extends ScalarMap, \IteratorAggregate
{
	public function get(string $name): null|ReadCookie|Cookie;

	public function set(Cookie $cookie): void;

	public function remove(ReadCookie|Cookie|string $nameOrCookie): void;
}
