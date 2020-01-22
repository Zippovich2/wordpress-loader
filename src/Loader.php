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

namespace WordpressWrapper\Loader;

use Symfony\Component\Dotenv\Dotenv;
use WordpressWrapper\Loader\Exception\MissingEnvException;

final class Loader
{
    private const REQUIRED_CONSTANTS = [
        'APP_ENV',
        'DB_NAME',
        'DB_USER',
        'DB_PASSWORD',
        'WP_HOME',
        'WP_SITEURL',
        'CONTENT_DIR',
    ];

    private const DEFAULT_CONSTANTS = [
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
     * @param string $wpCorePath path to WordPress core dir
     */
    public function load(string $wpCorePath = '/wp'): void
    {
        $this->addEnv('PROJECT_ROOT', \dirname($_SERVER['DOCUMENT_ROOT']));
        $this->addEnv('WEB_ROOT', $_SERVER['DOCUMENT_ROOT']);

        $this->dotenv->loadEnv($_ENV['PROJECT_ROOT'] . '/.env');

        $this->checkRequirements();
        $this->defineConstants();

        $this->addEnv('WP_CONTENT_DIR', $_ENV['WEB_ROOT'] . $_ENV['CONTENT_DIR']);
        $this->addEnv('WP_CONTENT_URL', $_ENV['WP_HOME'] . $_ENV['CONTENT_DIR']);
        $this->addEnv('ABSPATH', $_ENV['WEB_ROOT'] . $wpCorePath);

        $this->defineDefaultConstants();
    }

    /**
     * Create constant which required for debug and trying create log dir if it not exists.
     *
     * @param string $logPath path where log files should be created
     */
    public function debugSettings(string $logPath = '/var/log'): void
    {
        $this->addEnv('WP_DEBUG_DIR', $_ENV['PROJECT_ROOT'] . $logPath, true);
        $this->addEnv('WP_DEBUG_LOG', $_ENV['WP_DEBUG_DIR'] . \sprintf('/%s.log', $_ENV['APP_ENV']), true);

        if (!\file_exists($_ENV['WP_DEBUG_DIR'])) {
            \mkdir($_ENV['WP_DEBUG_DIR']);
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
     * @throws MissingEnvException when required env missing
     */
    private function checkRequirements(): void
    {
        foreach (self::REQUIRED_CONSTANTS as $requiredConstant) {
            if (!\array_key_exists($requiredConstant, $_ENV)) {
                throw new MissingEnvException($requiredConstant);
            }
        }
    }

    /**
     * Define constant depend on .env* files.
     */
    private function defineConstants(): void
    {
        $envVars = $this->parseEnvFile($_ENV['PROJECT_ROOT'] . '/.env');
        $envVars = $this->parseEnvFile($_ENV['PROJECT_ROOT'] . '/.env.local', $envVars);
        $envVars = $this->parseEnvFile($_ENV['PROJECT_ROOT'] . \sprintf('/.env.%s', $envVars['APP_ENV']), $envVars);
        $envVars = $this->parseEnvFile($_ENV['PROJECT_ROOT'] . \sprintf('/.env.%s.local', $envVars['APP_ENV']), $envVars);

        foreach ($envVars as $key => $value) {
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
     */
    private function parseEnvFile(string $path, array $oldEnvVars = []): array
    {
        $envVars = $this->dotenv->parse($this->getFileContent($path));

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
     * Convert string booleat values "true" and "false" to boolean.
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
     * @param string $key            env key
     * @param mixed  $value          env value
     * @param bool   $defineConstant if true define new constant
     */
    private function addEnv(string $key, $value, bool $defineConstant = false): void
    {
        $_ENV[$key] = $this->stringToBoolean($value);

        if ($defineConstant) {
            $this->defineConstant($key, $value);
        }
    }

    /**
     * Defining new constant if not defined.
     *
     * @param string $name  constant name
     * @param mixed  $value constant value
     */
    private function defineConstant(string $name, $value): void
    {
        if (!\defined($name)) {
            \define($name, $this->stringToBoolean($value));
        }
    }
}
