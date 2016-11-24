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

namespace HHIT\Doctrine\Migrations;

use Doctrine\ORM\EntityManagerInterface;
use HHIT\Doctrine\ORM\Contracts\EntityManagerConfigurationSource;
use Mockery\MockInterface;

class MigrationsConsoleHelperTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var MockInterface
     */
    private $entityManagerConfigurationSource;

    /**
     * @var MockInterface
     */
    private $entityManager;

    /**
     * @before
     */
    public function before()
    {
        $this->entityManagerConfigurationSource = \Mockery::mock(EntityManagerConfigurationSource::class);
        $this->entityManager = \Mockery::mock(EntityManagerInterface::class);
    }

    /**
     * @after
     */
    public function after()
    {
        \Mockery::close();
    }

    /**
     * @test
     */
    public function createFactory()
    {
        chdir(__DIR__.'/../../configs');
        $this->assertNotNull(MigrationsConsoleHelper::createConfigurationFactory($this->entityManagerConfigurationSource, $this->entityManager, 'doctrine.config.php'));
    }

    /**
     * @test
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Please configure Migrations
     */
    public function noConfig()
    {
        MigrationsConsoleHelper::createConfigurationFactory($this->entityManagerConfigurationSource, $this->entityManager);
    }
}
