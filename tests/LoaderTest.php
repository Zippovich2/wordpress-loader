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

namespace WordpressWrapper\Loader\Tests;

use PHPUnit\Framework\TestCase;
use WordpressWrapper\Loader\Exception\MissingEnvException;
use WordpressWrapper\Loader\Exception\ParseException;
use WordpressWrapper\Loader\Exception\PathException;
use WordpressWrapper\Loader\Loader;

/**
 * @author Roman Skoropadskyi <zipo.ckorop@gmail.com>
 */
final class LoaderTest extends TestCase
{
    public function testPathException1(): void
    {
        static::expectException(PathException::class);

        $loader = new Loader();
        $loader->load('/wp', __DIR__ . '/Fixtures/PathException/1');
    }

    public function testPathException2(): void
    {
        static::expectException(PathException::class);

        $loader = new Loader();
        $loader->load('/wp', __DIR__ . '/Fixtures/PathException/2');
    }

    public function testMissingEnvException(): void
    {
        static::expectException(MissingEnvException::class);

        $loader = new Loader();
        $loader->load('/wp', __DIR__ . '/Fixtures/MissingEnvException');
    }

    public function testParseException1(): void
    {
        static::expectException(ParseException::class);

        $loader = new Loader();
        $loader->load('/wp', __DIR__ . '/Fixtures/ParseException/1');
    }

    public function testParseException2(): void
    {
        static::expectException(ParseException::class);

        $loader = new Loader();
        $loader->load('/wp', __DIR__ . '/Fixtures/ParseException/2');
    }

    public function testCorrectData(): void
    {
        $projectRoot = __DIR__ . '/Fixtures/Successfull';
        $webRoot = $projectRoot . '/public';

        $loader = new Loader();
        $loader->load('/wp', $projectRoot, $webRoot);
        $loader->debugSettings();

        static::assertEquals(true, is_dir($projectRoot . '/var/log'));

        static::assertEquals(true, defined('WP_DEBUG_DIR'));
        static::assertEquals(true, defined('WP_DEBUG_LOG'));
        static::assertEquals(constant('WP_DEBUG_DIR'), $_ENV['WP_DEBUG_DIR']);
        static::assertEquals(constant('WP_DEBUG_LOG'), $_ENV['WP_DEBUG_LOG']);

        foreach (Loader::REQUIRED_CONSTANTS as $constant) {
            static::assertTrue(\defined($constant));
            static::assertTrue(isset($_ENV[$constant]));
            static::assertEquals(\constant($constant), $_ENV[$constant]);
        }
    }
}
