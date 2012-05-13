<?php

    /* This file is part of Console App.
     *
     * (c) 2012 Kevin Herrera
     *
     * For the full copyright and license information, please
     * view the LICENSE file that was distributed with this
     * source code.
     */

    use Symfony\Component\Console\Application as ConsoleApplication,
        Symfony\Component\Console\Input\InputInterface,
        Symfony\Component\Console\Output\OutputInterface;

    /**
     * Sets up the application.
     *
     * This class basically registers the commands for your
     * application.  You simply need to replace the code in
     * the method, {@link Application::registerCommands()}.
     *
     * @author Kevin Herrera <me@kevingh.com>
     */
    class Application extends ConsoleApplication
    {
        /** {@inheritDoc} */
        public function __construct ()
        {
            parent::__construct(
                'PHP Compiler',
                '1.0-dev'
            );
        }

        /** {@inheritDoc} */
        public function doRun (InputInterface $input, OutputInterface $output)
        {
            $this->registerCommands();

            return parent::doRun($input, $output);
        }

        /**
         * Registers the command for the application.
         *
         * This method will register our custom commands with the Application
         * console class.  By default, a simple example command is registered.
         * You will need to replace the contents of this method will all the
         * command you will want to make available from this application.
         */
        private function registerCommands ()
        {
            $this->add(new KevinGH\Compiler\Command\Compile);
        }
    }