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

namespace HHIT\Doctrine\Fixtures;

use Doctrine\Common\DataFixtures\Executor\ORMExecutor;
use Doctrine\Common\DataFixtures\Loader;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\ORM\EntityManagerInterface;
use HHIT\Doctrine\Fixtures\Contracts\ORMFixtureHandler;
use HHIT\Doctrine\Fixtures\Contracts\ORMFixtureLogger;

class ORMDefaultFixtureHandler implements ORMFixtureHandler
{
    /**
     * @var ORMPurger
     */
    private $purger;

    /**
     * @var ORMExecutor
     */
    private $executor;

    /**
     * @var ORMFixtureLogger
     */
    private $logger;

    public function __construct(EntityManagerInterface $entityManager, ORMFixtureLogger $logger = null)
    {
        $this->purger = new ORMPurger($entityManager);
        $this->executor = new ORMExecutor($entityManager, $this->purger);
        $this->logger = $logger === null ? new ORMFixtureNoOpLogger() : $logger;
        $this->executor->setLogger(function ($message) {
            $this->logger->log($message);
        });
    }

    public function load($fixtures, $append = true, $purgeWithTruncate = false)
    {
        if (!$fixtures) {
            throw new \InvalidArgumentException('Fixtures required!');
        }

        $paths = is_array($fixtures) ? $fixtures : [$fixtures];
        $loader = new Loader();
        foreach ($paths as $path) {
            if (is_dir($path)) {
                $loader->loadFromDirectory($path);
            } else {
                $loader->loadFromFile($path);
            }
        }
        if (empty($loader->getFixtures())) {
            throw new \InvalidArgumentException(sprintf('Could not find any fixtures to load in: %s', "\n\n".implode('\n-', $paths)));
        }

        $this->purger->setPurgeMode($purgeWithTruncate ? ORMPurger::PURGE_MODE_TRUNCATE : ORMPurger::PURGE_MODE_DELETE);
        $this->executor->execute($loader->getFixtures(), $append);
    }

    public function purge($purgeWithTruncate = false)
    {
        $this->purger->setPurgeMode($purgeWithTruncate ? ORMPurger::PURGE_MODE_TRUNCATE : ORMPurger::PURGE_MODE_DELETE);
        $this->purger->purge();
    }
}
