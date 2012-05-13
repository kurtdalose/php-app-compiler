<?php

    /* This file is part of PHP Compiler.
     *
     * (c) 2012 Kevin Herrera
     *
     * For the full copyright and license information, please
     * view the LICENSE file that was distributed with this
     * source code.
     */

    namespace KevinGH\Compiler\Command;

    use SplFileInfo,
        Symfony\Component\Console\Command\Command,
        Symfony\Component\Console\Input\InputArgument,
        Symfony\Component\Console\Input\InputInterface,
        Symfony\Component\Console\Input\InputOption,
        Symfony\Component\Console\Output\OutputInterface,
        Symfony\Component\Finder\Finder,
        Symfony\Component\Yaml\Yaml;

    /**
     * The source compiler command.
     *
     * This command will use the given arguments to compile the source code.
     *
     * @author Kevin Herrera <me@kevingh.com>
     */
    class Compile extends Command
    {
        /**
         * The compile output stream.
         *
         * @type resource
         */
        private $output;

        /** {@inheritDoc} */
        public function configure ()
        {
            $this->setName('compile');
            $this->setDescription('Compiles the PHP source code.');

            $this->addOption(
                'output',
                'o',
                InputOption::VALUE_REQUIRED,
                'The output file name'
            );

            $this->addArgument(
                'config',
                InputArgument::REQUIRED,
                'The build configuration file.'
            );

            $this->addArgument(
                'stub',
                InputArgument::OPTIONAL,
                'The build stub file.'
            );
        }

        /** {@inheritDoc} */
        public function execute (InputInterface $input, OutputInterface $output)
        {
            if (false === function_exists('bcompiler_write_file'))
            {
                $output->writeln(
                    '<error>The bcompiler extension is not available.</error>'
                );

                return 1;
            }

            $config = $input->getArgument('config');
            $stub = $input->getArgument('stub');
            $out = $input->getOption('output') ?: 'p.out';

            if (false === file_exists($config))
            {
                $output->writeln(
                    '<error>The build configuration file could not be found.</error>'
                );

                return 1;
            }

            if ($stub && (false === file_exists($stub)))
            {
                $output->writeln(
                    '<error>The stub file could not be found.</error>'
                );

                return 1;
            }

            $config = Yaml::parse($config);

            if (empty($config))
            {
                $output->writeln(
                    '<error>The build configuration file is empty.</error>'
                );

                return 1;
            }

            if (OutputInterface::VERBOSITY_VERBOSE === $output->getVerbosity())
            {
                $output->writeln('Compiling...');
            }

            if (false === $this->startCompile($out))
            {
                return 1;
            }

            foreach ($this->getFiles($config) as $file)
            {
                if (OutputInterface::VERBOSITY_VERBOSE === $output->getVerbosity())
                {
                    $output->writeln(' - ' . $file->getRealPath());
                }

                if (false === $this->addFile($file))
                {
                    return 1;
                }
            }

            if ($stub)
            {
                $file = new SplFileInfo ($stub);

                if (OutputInterface::VERBOSITY_VERBOSE === $output->getVerbosity())
                {
                    $output->writeln(' * ' . $file->getRealPath());
                }

                if (false === $this->addFile($file))
                {
                    return 1;
                }
            }

            if (false === $this->stopCompile())
            {
                return 1;
            }

            if (OutputInterface::VERBOSITY_VERBOSE === $output->getVerbosity())
            {
                $output->writeln('Done.');
            }
        }

        /**
         * Adds the file to the compiled output.
         *
         * This method will add the file to the compiled output.
         *
         * @param SplFileInfo $file The file object.
         * @return boolean TRUE if successful, FALSE if not.
         */
        private function addFile (SplFileInfo $file)
        {
            if (false === bcompiler_write_file($this->output, $file->getRealPath()))
            {
                return false;
            }

            return true;
        }

        /**
         * Returns the files found using the build settings.
         *
         * This method will return the files found using the build settings.
         *
         * @param array $config The build configuration.
         * @return boolean TRUE if successful, FALSE if not.
         */
        private function getFiles (array $config)
        {
            $files = array();

            foreach ($config as $methods)
            {
                $finder = new Finder;

                foreach ($methods as $method)
                {
                    if (is_string($method))
                    {
                        $finder->$method();
                    }

                    elseif (is_array($method))
                    {
                        $args = current(array_values($method));
                        $method = current(array_keys($method));

                        call_user_func_array(
                            array($finder, $method),
                            (array) $args
                        );
                    }

                    else
                    {
                        $output->writeln(
                            '<error>Invalid build configuration file.</error>'
                        );

                        fclose($this->output);

                        return 1;
                    }
                }

                foreach ($finder as $file)
                {
                    $files[] = $file;
                }
            }

            return $files;
        }

        /**
         * Creates a new compile output stream.
         *
         * This method will create a new compile output stream.
         *
         * @param string $output The output file path.
         * @return boolean TRUE if successful, FALSE if not.
         */
        private function startCompile ($output)
        {
            if (file_exists($output))
            {
                if (false === unlink($output))
                {
                    return false;
                }
            }

            if (false === ($this->output = fopen($output, 'w')))
            {
                return false;
            }

            if (false === bcompiler_write_header($this->output))
            {
                return false;
            }

            return true;
        }

        /**
         * Finishes the compile output stream.
         *
         * This method will finish the compile output stream.
         *
         * @return boolean TRUE if successful, FALSE if not.
         */
        private function stopCompile ()
        {
            if (false === bcompiler_write_footer($this->output))
            {
                return false;
            }

            if (false === fclose($this->output))
            {
                return false;
            }

            return true;
        }
    }