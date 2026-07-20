<?php

namespace Config;

/**
 * ---------------------------------------------------------------
 * SYSTEM FOLDER NAME
 * ---------------------------------------------------------------
 *
 * This variable must contain the name of your "system" directory.
 * Include the path if the directory is not in the same directory
 * as this file.
 */
class Paths
{
    /**
     * ---------------------------------------------------------------
     * SYSTEM DIRECTORY
     * ---------------------------------------------------------------
     * The CodeIgniter framework system directory (installed via Composer).
     */
    public string $systemDirectory = __DIR__ . '/../../vendor/codeigniter4/framework/system';

    /**
     * ---------------------------------------------------------------
     * APPLICATION DIRECTORY
     * ---------------------------------------------------------------
     */
    public string $appDirectory = __DIR__ . '/..';

    /**
     * ---------------------------------------------------------------
     * WRITABLE DIRECTORY
     * ---------------------------------------------------------------
     * Logs, sessions, cache, SQLite database.
     */
    public string $writableDirectory = __DIR__ . '/../../writable';

    /**
     * ---------------------------------------------------------------
     * TESTS DIRECTORY
     * ---------------------------------------------------------------
     */
    public string $testsDirectory = __DIR__ . '/../../tests';

    /**
     * ---------------------------------------------------------------
     * VIEW DIRECTORY
     * ---------------------------------------------------------------
     */
    public string $viewDirectory = __DIR__ . '/../Views';
}
