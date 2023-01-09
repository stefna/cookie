<?php declare(strict_types=1);

namespace Stefna\Cookie;

enum SameSite
{
	/**
	 * SameSite policy `None`.
	 */
	case None;

	/**
	 * SameSite policy `Lax`.
	 */
	case Lax;

	/**
	 * SameSite policy `Strict`.
	 */
	case Strict;
}
