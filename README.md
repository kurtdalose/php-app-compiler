# PHP Compiler

Compiles one or more PHP source files into a PHP bytecode file using bcompiler.

## Usage

    Usage: phpc.php LIST [OUTPUT]

    Compiles PHP source files using bcompiler.

    LIST
        The file that contains a list of PHP source files to compile together.
        The list consists of one source file per path.  If relative paths are
        used, the location of the list file will be used as the starting path.

    OUTPUT
        The name of the output file.

## Notes

The compiler is designed so that you can compile it if you want.

    $ echo 'phpc.php' > compile.list
    $ php phpc.php compile.list phpc.pbc

And now you have a bytecode version of the compiler.