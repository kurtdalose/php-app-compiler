<?php

    /* This file is part of Compiler.
     *
     * (c) 2012 Kevin Herrera
     *
     * For the full copyright and license information, please
     * view the LICENSE file that was distributed with this
     * source code.
     */

    namespace KevinGH\Compiler\Command;

    use KevinGH\Compiler\Command\Compile,
        PHPUnit_Framework_TestCase,
        Symfony\Component\Console\Application,
        Symfony\Component\Console\Output\OutputInterface,
        Symfony\Component\Console\Tester\CommandTester,
        Symfony\Component\Yaml\Yaml;

    class CompileTest extends PHPUnit_Framework_TestCase
    {
        private $dir;

        protected function destroy ($dir)
        {
            foreach (scandir($dir) as $item)
            {
                if (('.' == $item) || ('..' == $item))
                {
                    continue;
                }

                $path = "$dir/$item";

                if (is_dir($path))
                {
                    $this->destroy($path);
                }

                else
                {
                    unlink($path);
                }
            }

            rmdir($dir);
        }

        protected function setUp ()
        {
            unlink($this->dir = tempnam(sys_get_temp_dir(), 'php'));

            mkdir($this->dir);
            mkdir($this->dir . '/source');
        }

        protected function tearDown ()
        {
            $this->destroy($this->dir);
        }

        public function testCompile ()
        {
            file_put_contents($this->dir . '/build.yml', Yaml::dump(array(
                array(
                    'files',
                    array('name' => '*.php'),
                    array('in' => $this->dir . '/source')
                )
            )));

            mkdir($this->dir . '/source/KevinGH/Compiler/Test', 0755, true);

            file_put_contents(
                $this->dir . '/source/KevinGH/Compiler/Test/Test.php',
                <<<SOURCE
<?php

    namespace KevinGH\Compiler\Test;

    class Test
    {
        public static function doTest ()
        {
            echo "Compiled result executed successfully.\n";
        }
    }
SOURCE
);

            file_put_contents($this->dir . '/stub.php', <<<STUB
<?php

    use KevinGH\Compiler\Test\Test;

    Test::doTest();
STUB
);

            $app = new Application;

            $app->add(new Compile);

            $command = $app->find('compile');

            $tester = new CommandTester ($command);

            $tester->execute(array(
                '--output' => $this->dir . '/test.out',
                'config' => $this->dir . '/build.yml',
                'stub' => $this->dir . '/stub.php'
            ));

            $this->assertEquals(
                '',
                $tester->getDisplay()
            );

            $tester->execute(array(
                '--output' => $this->dir . '/test.out',
                'config' => $this->dir . '/build.yml',
                'stub' => $this->dir . '/stub.php'
            ), array(
                'verbosity' => OutputInterface::VERBOSITY_VERBOSE
            ));

            $this->assertEquals(
                "Compiling...
 - {$this->dir}/source/KevinGH/Compiler/Test/Test.php
 * {$this->dir}/stub.php
Done.
",
                $tester->getDisplay()
            );

            ob_start();

            system('php ' . $this->dir . '/test.out');

            $this->assertEquals(
                "Compiled result executed successfully.\n",
                ob_get_clean()
            );
        }

        public function testCompileMissingConfig ()
        {
            $app = new Application;

            $app->add(new Compile);

            $command = $app->find('compile');

            $tester = new CommandTester ($command);

            $tester->execute(array(
                'config' => $this->dir . '/build.yml',
                'stub' => $this->dir . '/stub.php'
            ));

            $this->assertRegExp(
                '/The build configuration file could not be found/',
                $tester->getDisplay()
            );
        }

        public function testCompileMissingStub ()
        {
            touch($this->dir . '/build.yml');

            $app = new Application;

            $app->add(new Compile);

            $command = $app->find('compile');

            $tester = new CommandTester ($command);

            $tester->execute(array(
                'config' => $this->dir . '/build.yml',
                'stub' => $this->dir . '/stub.php'
            ));

            $this->assertRegExp(
                '/The stub file could not be found/',
                $tester->getDisplay()
            );
        }

        public function testCompileEmptyConfig ()
        {
            touch($this->dir . '/build.yml');
            touch($this->dir . '/stub.php');

            $app = new Application;

            $app->add(new Compile);

            $command = $app->find('compile');

            $tester = new CommandTester ($command);

            $tester->execute(array(
                'config' => $this->dir . '/build.yml',
                'stub' => $this->dir . '/stub.php'
            ));

            $this->assertRegExp(
                '/The build configuration file is empty/',
                $tester->getDisplay()
            );
        }
    }