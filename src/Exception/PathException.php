<?php

declare(strict_types=1);

/*
 * This file is part of the "Wordpress Wrapper Loader" package.
 *
 * (c) Skoropadskyi Roman <zipo.ckorop@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace WordpressWrapper\Loader\Exception;

/**
 * @author Roman Skoropadskyi <zipo.ckorop@gmail.com>
 */
class PathException extends \RuntimeException
{
    public function __construct(string $message, int $code = 0, \Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
