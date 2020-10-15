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

namespace WordpressWrapper\Loader;

use Symfony\Component\Dotenv\Dotenv;
use Symfony\Component\Dotenv\Exception\FormatException;
use Symfony\Component\Dotenv\Exception\PathException as DotEnvPathException;
use WordpressWrapper\Loader\Exception\MissingConstantException;
use WordpressWrapper\Loader\Exception\ParseException;
use WordpressWrapper\Loader\Exception\PathException;

/**
 * @author Roman Skoropadskyi <zipo.ckorop@gmail.com>
 */
final class Loader
{
    public const REQUIRED_CONSTANTS = [
        'DB_NAME',
        'DB_USER',
        'DB_PASSWORD',
        'WP_HOME',
        'WP_SITEURL',
        'CONTENT_DIR',
    ];

    public const DEFAULT_CONSTANTS = [
        'DB_HOST' => 'localhost',
        'DB_CHARSET' => 'utf8',
        'DB_COLLATE' => '',
        'AUTH_KEY' => 'unique phrase',
        'SECURE_AUTH_KEY' => 'unique phrase',
        'LOGGED_IN_KEY' => 'unique phrase',
        'NONCE_KEY' => 'unique phrase',
        'AUTH_SALT' => 'unique phrase',
        'SECURE_AUTH_SALT' => 'unique phrase',
        'LOGGED_IN_SALT' => 'unique phrase',
        'NONCE_SALT' => 'unique phrase',
        'WP_DEBUG' => true,
        'AUTOMATIC_UPDATER_DISABLED' => true,
        'DISABLE_WP_CRON' => false,
        'DISALLOW_FILE_EDIT' => true,
        'DISALLOW_FILE_MODS' => true,
        'WP_USE_THEMES' => true,
        'DB_PREFIX' => 'wp_',
    ];

    /**
     * @var Dotenv
     */
    private $dotenv;

    public function __construct()
    {
        $this->dotenv = new Dotenv();
    }

    /**
     * Loading env variables and define all required constants.
     *
     * @param string      $wpCorePath      path to WordPress core dir relative to document root
     * @param string|null $projectRootPath path to the project root, project root must contain .env file.
     * @param string|null $publicDirPath   path to the index.php
     *
     * @throws PathException  when a .env file does not exist or is not readable.
     * @throws ParseException when .env file has a syntax error.
     */
    public function load(string $wpCorePath = '/wp', ?string $projectRootPath = null, ?string $publicDirPath = null): void
    {
        $this->addEnv('PROJECT_ROOT', $projectRootPath ?? $_ENV['PROJECT_ROOT'] ?? \dirname($_SERVER['DOCUMENT_ROOT']));
        $this->addEnv('WEB_ROOT', $publicDirPath ?? $_ENV['WEB_ROOT'] ?? $_SERVER['DOCUMENT_ROOT']);

        try {
            $this->dotenv->loadEnv($_ENV['PROJECT_ROOT'] . '/.env');
        } catch (DotEnvPathException $e) {
            throw new PathException($e->getMessage(), $e->getCode(), $e);
        } catch (FormatException $e) {
            throw new ParseException($e->getMessage(), $e->getCode(), $e);
        }

        $this->defineConstants($_ENV['APP_ENV']);
        $this->checkRequirements();

        $this->defineConstant('WP_CONTENT_DIR', $_ENV['WEB_ROOT'] . CONTENT_DIR);
        $this->defineConstant('WP_CONTENT_URL', WP_HOME . CONTENT_DIR);
        $this->defineConstant('ABSPATH', $_ENV['WEB_ROOT'] . $wpCorePath . '/');

        if (!\is_dir(ABSPATH)) {
            throw new PathException(\sprintf('Unable to find wordpress core directory "%s".', ABSPATH));
        }

        $this->defineDefaultConstants();
    }

    /**
     * Create constant which required for debug and trying create log dir if it not exists.
     *
     * @param string $logPath path where log files should be created
     */
    public function debugSettings(string $logPath = '/var/log'): void
    {
        $this->defineConstant('WP_DEBUG_DIR', $_ENV['PROJECT_ROOT'] . $logPath);
        $this->defineConstant('WP_DEBUG_LOG', WP_DEBUG_DIR . \sprintf('/%s.log', $_ENV['APP_ENV']));

        if (!\file_exists(WP_DEBUG_DIR)) {
            \mkdir(WP_DEBUG_DIR, 0777, true);
        }
    }

    /**
     * Default constants with default values if it not defined.
     */
    private function defineDefaultConstants(): void
    {
        foreach (self::DEFAULT_CONSTANTS as $constantName => $value) {
            $this->defineConstant($constantName, $value);
        }
    }

    /**
     * Check if all required environment variables are exist in .env* files.
     *
     * @throws MissingConstantException when required env missing
     */
    private function checkRequirements(): void
    {
        foreach (self::REQUIRED_CONSTANTS as $constantName) {
            if (!\defined($constantName)) {
                throw new MissingConstantException($constantName);
            }
        }
    }

    /**
     * Define constant depend on .env* files.
     */
    private function defineConstants(string $appEnv): void
    {
        $constants = $this->parseEnvFile($_ENV['PROJECT_ROOT'] . '/.const');
        $constants = $this->parseEnvFile($_ENV['PROJECT_ROOT'] . '/.const.local', $constants);
        $constants = $this->parseEnvFile($_ENV['PROJECT_ROOT'] . \sprintf('/.const.%s', $appEnv), $constants);
        $constants = $this->parseEnvFile($_ENV['PROJECT_ROOT'] . \sprintf('/.const.%s.local', $appEnv), $constants);

        foreach ($constants as $key => $value) {
            $this->defineConstant($key, $value);
        }
    }

    /**
     * Parsing env file and merge values with $oldEnvVars.
     *
     * @param string $path       path to .env file.
     * @param array  $oldEnvVars values which need merge with new values
     *
     * @return array return parsed and merged values
     *
     * @throws ParseException when .evn* files contain syntax error.
     */
    private function parseEnvFile(string $path, array $oldEnvVars = []): array
    {
        try {
            $envVars = $this->dotenv->parse($this->getFileContent($path));
        } catch (FormatException $e) {
            throw new ParseException($e->getMessage(), $e->getCode(), $e);
        }

        return \array_merge($oldEnvVars, $envVars);
    }

    /**
     * Return file content or empty string.
     *
     * @param string $path path to file
     *
     * @return string return file content or empty string
     */
    private function getFileContent(string $path): string
    {
        if (\file_exists($path)) {
            return \file_get_contents($path);
        }

        return '';
    }

    /**
     * Convert string boolean values "true" and "false" to boolean.
     *
     * @param $value
     *
     * @return bool
     */
    private function stringToBoolean($value)
    {
        switch ($value) {
            case 'true':
                return true;
            case 'false':
                return false;
            default:
                return $value;
        }
    }

    /**
     * Adding new env value.
     *
     * @param string $key   env key
     * @param mixed  $value env value
     */
    private function addEnv(string $key, $value): void
    {
        $_ENV[$key] = $this->stringToBoolean($value);
    }

    /**
     * Defining new constant if not defined.
     *
     * @param string $name  constant name
     * @param mixed  $value constant value
     */
    private function defineConstant(string $name, $value): void
    {
        var_dump($value);
        if (!\defined($name)) {
            \define($name, $this->stringToBoolean($value));
        }
    }
}
