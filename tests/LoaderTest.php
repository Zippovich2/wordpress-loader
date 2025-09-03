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
use WordpressWrapper\Loader\Exception\MissingConstantException;
use WordpressWrapper\Loader\Exception\ParseException;
use WordpressWrapper\Loader\Exception\PathException;
use WordpressWrapper\Loader\Loader;

/**
 * @author Roman Skoropadskyi <zipo.ckorop@gmail.com>
 */
final class LoaderTest extends TestCase
{
    public const PUBLIC_DIR = __DIR__ . '/Fixtures/public';

    protected function tearDown(): void
    {
        $_ENV = [];
    }

    public function testMissingConstException(): void
    {
        static::expectException(MissingConstantException::class);

        $loader = new Loader();
        $loader->load('/wp', __DIR__ . '/Fixtures/MissingConstException', self::PUBLIC_DIR);
    }

    /**
     * @dataProvider parseExceptionProvider
     */
    public function testParseException($path): void
    {
        static::expectException(ParseException::class);

        $loader = new Loader();
        $loader->load('/wp', $path, self::PUBLIC_DIR);
    }

    /**
     * @dataProvider pathExceptionProvider
     */
    public function testPathException($wpCorePath, $projectRoot): void
    {
        self::expectException(PathException::class);

        $loader = new Loader();
        $loader->load($wpCorePath, $projectRoot);
    }

    public function testSuccessful(): void
    {
        $projectRoot = __DIR__ . '/Fixtures/Successfull';

        $loader = new Loader();
        $loader->load('/wp', $projectRoot, self::PUBLIC_DIR);
        $loader->debugSettings('/debug');

        static::assertTrue(is_dir($projectRoot . '/debug'));

        if (file_exists($projectRoot . '/debug')) {
            rmdir($projectRoot . '/debug');
        }

        static::assertTrue(\defined('WP_DEBUG_DIR'));
        static::assertTrue(\defined('WP_DEBUG_LOG'));
        static::assertEquals(\constant('WP_DEBUG_DIR'), $_ENV['PROJECT_ROOT'] . '/debug');
        static::assertEquals(\constant('WP_DEBUG_LOG'), WP_DEBUG_DIR . \sprintf('/%s.log', $_ENV['APP_ENV']));

        foreach (Loader::REQUIRED_CONSTANTS as $constant) {
            static::assertTrue(\defined($constant));
        }
    }

    public static function pathExceptionProvider()
    {
        return [
            ['/wp', __DIR__ . '/Fixtures/PathException/1'],
            ['/wordpress', __DIR__ . '/Fixtures/PathException/2'],
        ];
    }

    public static function parseExceptionProvider()
    {
        return [
            [__DIR__ . '/Fixtures/ParseException/1'],
            [__DIR__ . '/Fixtures/ParseException/2'],
            [__DIR__ . '/Fixtures/ParseException/3'],
        ];
    }
}
