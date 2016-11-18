<?php

/*
 * The MIT License (MIT)
 *
 * Copyright (c) 2016 Hendrik Heneke
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */

namespace HHIT\Doctrine\Fixtures\Console\Commands;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class LoadORMFixturesCommand.
 *
 * @codeCoverageIgnore
 */
class LoadORMFixturesCommand extends AbstractORMFixturesCommand
{
    private static $OPT_APPEND = 'append';
    private static $OPT_FIXTURES = 'fixtures';

    public function configure()
    {
        parent::configure();
        $this->setName('fixtures:orm:load')
            ->setDescription('Loads fixtures to your database')
            ->addOption(self::$OPT_APPEND, null, InputOption::VALUE_NONE, 'Append the data fixtures instead of deleting all data from the database first.')
            ->addOption(self::$OPT_FIXTURES, null, InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY, 'The directory or file to load data fixtures from.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $append = $this->isAppend($input);
        $fixtures = $input->getOption(self::$OPT_FIXTURES);

        if ($input->isInteractive() && !$append) {
            if (!$this->isPurgeConfirmed($input, $output)) {
                return;
            }
        }

        $this->createHandler($output)->load($fixtures, $append, $this->isPurgeWithTruncate($input));
    }

    private function isAppend(InputInterface $input)
    {
        return $input->getOption(self::$OPT_APPEND);
    }
}
