<?php

/*
 * The MIT License (MIT)
 *
 * Copyright (c) 2019 Hendrik Heneke
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

use Doctrine\ORM\EntityManagerInterface;
use HHIT\Doctrine\Fixtures\Console\ORMFixtureConsoleLogger;
use HHIT\Doctrine\Fixtures\ORMDefaultFixtureHandler;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;

/**
 * Class AbstractORMFixturesCommand.
 *
 * @codeCoverageIgnore
 */
abstract class AbstractORMFixturesCommand extends Command
{
    protected static $OPT_PURGE_TRUNCATE = 'purge-with-truncate';

    public function configure()
    {
        $this
            ->addOption(self::$OPT_PURGE_TRUNCATE, null, InputOption::VALUE_NONE, 'Purge data by using a database-level truncate.');
    }

    /**
     * @return EntityManagerInterface
     */
    protected function getEntityManager()
    {
        return $this->getHelper('em')->getEntityManager();
    }

    protected function askConfirmation(InputInterface $input, OutputInterface $output, $question, $default)
    {
        $questionHelper = $this->getHelperSet()->get('question');
        $question = new ConfirmationQuestion($question, $default);

        return $questionHelper->ask($input, $output, $question);
    }

    /**
     * @param OutputInterface $output
     *
     * @return ORMDefaultFixtureHandler
     */
    protected function createHandler(OutputInterface $output)
    {
        return new ORMDefaultFixtureHandler($this->getEntityManager(), new ORMFixtureConsoleLogger($output));
    }

    protected function isPurgeWithTruncate(InputInterface $input)
    {
        return $input->getOption(self::$OPT_PURGE_TRUNCATE);
    }

    protected function isPurgeConfirmed(InputInterface $input, OutputInterface $output)
    {
        if ($input->isInteractive()) {
            return $this->askConfirmation($input, $output, '<question>Careful, database will be purged. Do you want to continue (y/n)?</question>', false);
        } else {
            return true;
        }
    }
}
