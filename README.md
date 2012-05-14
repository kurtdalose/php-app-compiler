# PHP Compiler

[![Build Status](https://secure.travis-ci.org/kherge/php-compiler.png?branch=master)](http://travis-ci.org/kherge/php-compiler)

This console application makes using the PHP bcompiler extension a
little simpler.  The application itself can be compiled into a PHAR
executable.  The application cannot compile itself, yet.

## Installing

1. Clone the repository

    $ git clone https://github.com/kherge/php-compiler

2. Build the PHAR application

    $ cd php-compiler
    $ bin/compile

3. Place the PHAR application in your bin directory and make it executable

    $ mv bin/phpc.phar ~/bin
    $ cd ~/bin
    $ chmod 755 phpc.phar
    $ ln -s phpc.phar phpc

## Usage

The help screen

    $ phpc help compile
    Usage:
     compile [-o|--output="..."] config [stub]

    Arguments:
     config        The build configuration file.
     stub          The build stub file.

    Options:
     --output (-o) The output file name

- If no output is specified, the file name "p.out" will be used.
- The build configuration file is a YAML file.
- The build stub file is what gets executed when your run the output.

## Build Configuration

The YAML build configuration file is simply used to determine what
files needed to be compiled.  The configuration settings are actually
a series of method calls and their arguments

PHP (borrowed from Composer)

    $finder = new Symfony\Component\Finder\Finder;

    $finder->files()
           ->ignoreVCS(true)
           ->name('*.php')
           ->notName('Compiler.php')
           ->notName('ClassLoader.php')
           ->in(__DIR__.'/..');

    // add files to $files

    $finder = new Symfony\Component\Finder\Finder;

    $finder->files()
           ->ignoreVCS(true)
           ->name('*.php')
           ->exclude('Tests')
           ->in(__DIR__.'/../../vendor/symfony/')
           ->in(__DIR__.'/../../vendor/seld/jsonlint/src/')
           ->in(__DIR__.'/../../vendor/justinrainbow/json-schema/src/')

YAML equivalent

    -
        - files
        - ignoreVCS: true
        - name: '*.php'
        - notName: Compiler.php
        - notName: ClassLoader.php
        - in: '/..'
    -
        - files
        - ignoreVCS: true
        - name: '*.php'
        - exclude: Tests
        - in: /../../vendor/symfony/
        - in: /../../vendor/seld/jsonlint/src/
        - in: /../../vendor/justinrainbow/json-schema/src/