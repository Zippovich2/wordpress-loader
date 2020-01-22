<?php

declare(strict_types=1);

/*
 * This file is part of the "Wordpress Wrapper" package.
 *
 * (c) Skoropadskyi Roman <zipo.ckorop@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace WordpressWrapper\Loader\Exception;

class MissingEnvException extends \DomainException
{
    public function __construct(string $env, int $code = 0, \Throwable $previous = null)
    {
        parent::__construct(\sprintf('Missing env "%s".', $env), $code, $previous);
    }
}
