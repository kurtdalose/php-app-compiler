<?php

    /* This file is part of PHP Compiler.
     *
     * (c) 2012 Kevin Herrera
     *
     * For the full copyright and license information, please
     * view the LICENSE file that was distributed with this
     * source code.
     */

    if (false === defined('NAME'))
    {
        define('NAME', basename($_SERVER['argv'][0]));
    }

    set_error_handler(function ($code, $message, $file, $line)
    {
        fputs(STDERR, NAME . ": $message\n");

        exit(1);
    });

    set_exception_handler(function ($exception)
    {
        fputs(STDERR, NAME . ': ' . $exception->getMessage() . "\n");

        exit(1);
    });

    if (false === function_exists('bcompiler_write_file'))
    {
        trigger_error('The bcompiler extension is not available.');
    }

    if ((2 > $_SERVER['argc'])
            || in_array('h', $_SERVER['argv'])
            || in_array('help', $_SERVER['argv']))
    {
        $name = NAME;

        echo <<<USAGE
Usage: $name LIST [OUTPUT]

Compiles PHP source files using bcompiler.

LIST
    The file that contains a list of PHP source files to compile together.
    The list consists of one source file per path.  If relative paths are
    used, the location of the list file will be used as the starting path.

OUTPUT
    The name of the output file.

USAGE;

        exit();
    }

    if (false === file_exists($_SERVER['argv'][1]))
    {
        trigger_error('The list file does not exist: ' . $_SERVER['argv'][1]);
    }

    if (empty($_SERVER['argv'][2]))
    {
        $_SERVER['argv'][] = 'out.pbc';
    }

    if (file_exists($_SERVER['argv'][2]))
    {
        unlink($_SERVER['argv'][2]);
    }

    $files = call_user_func(
        function ($file)
        {
            $relative = realpath(dirname($file));

            $list = array();

            $fp = fopen($file, 'r');

            while ($path = fread($fp, 4096))
            {
                $path = trim($path);

                if (false === ($true = realpath($path)))
                {
                    $true = $relative . DIRECTORY_SEPARATOR . $path;

                    if (false === ($true = realpath($true)))
                    {
                        fclose($fp);

                        trigger_error("A file in the list does not exist: $path");
                    }
                }

                $list[] = $true;
            }

            fclose($fp);

            return $list;
        },
        $_SERVER['argv'][1]
    );

    $fh = fopen($_SERVER['argv'][2], 'w');

    bcompiler_write_header($fh);

    foreach ($files as $file)
    {
        bcompiler_write_file($fh, $file);
    }

    bcompiler_write_footer($fh);

    fclose($fh);