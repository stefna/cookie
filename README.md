# Cookies for psr-15 and psr-7

The package helps in working with HTTP cookies in a PSR-7/PSR-15 environment.

## Requirements

PHP 8.2 or higher.

## Installation

```bash
composer require stefna/cookie
```

## Motivation

When using PSR-7 the `setcookie()` function becomes useless when you want
everything connected to the request-response loop to be in the Request and
Response objects.

`setcookie()` also have a tendency to not support the latest features of the
http cookie standard.

## Concept

The main concept of package is the separation of read/write of cookie from
using the cookie so with that in mind the `CookieJar` is not connected to the
http layer it's just a container that stores cookies.

It's then up to the `CookieMiddleware` and `CookieManager` to read and write
the http headers.

By separating it like this it's easy to implement Signing or encryption of the
cookies since it's not part of the code that uses the cookies.

It also means you don't need the Response object when creating new cookies all
you need to do is add the cookie to the `CookieJar` and the middleware deals
with writing the cookie

## Usage

Usage of cookie jar from inside action.

The `CookieJar` is added to the request by the `CookieMiddleware` that
middleware also deals with adding the cookies to the response headers.

```php
use Psr\Http\Message\ServerRequestInterface;

class Action
{
	public function __invoke(ServerRequestInterface $request)
	{
		$cookieJar = $request->getAttribute(\Stefna\Cookie\CookieJar::class);

		if (!$cookieJar->has('visits')) {
			$cookieJar->set(new \Stefna\Cookie\Cookie('visits', 0));
		}
		$visitCookie = $cookieJar->get('visits');
		if ($cookieJar->getInt('visits') < 10) {
			$cookieJar->set($visitCookie
				->withValue($cookieJar->getInt('visits') + 1)
				->withExpires(DateInterval::createFromDateString('+1 week'))
			);
		}
		else {
			// remove cookie alt 1
			$cookieJar->set($visitCookie->expire());
			// remove cookie alt 2
			$cookieJar->remove($visitCookie);
		}
	}
}
```

## Contribute

We are always happy to receive bug/security reports and bug/security fixes

## License

The MIT License (MIT). Please see [License File](LICENSE) for more information.

