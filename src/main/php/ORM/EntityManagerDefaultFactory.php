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

namespace HHIT\Doctrine\ORM;

use Doctrine\Common\Annotations\AnnotationRegistry;
use Doctrine\Common\EventManager;
use Doctrine\DBAL\Types\Type;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use HHIT\Doctrine\DBAL\Contracts\DBALConnectionFactory;
use HHIT\Doctrine\ORM\Contracts\EntityManagerConfigurationFactory;
use HHIT\Doctrine\ORM\Contracts\EntityManagerFactory;
use HHIT\Doctrine\Types\CarbonDateTimeType;
use HHIT\Doctrine\Types\CarbonDateTimeTzType;
use HHIT\Doctrine\Types\CarbonDateType;
use HHIT\Doctrine\Types\CarbonTimeType;

class EntityManagerDefaultFactory implements EntityManagerFactory
{
    /**
     * @var EntityManagerConfigurationFactory
     */
    private $configurationFactory;

    /**
     * @var DBALConnectionFactory
     */
    private $connectionFactory;

    private $eventManager;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    public function __construct(EntityManagerConfigurationFactory $configurationFactory, DBALConnectionFactory $connectionFactory, EventManager $eventManager = null)
    {
        $this->configurationFactory = $configurationFactory;
        $this->connectionFactory = $connectionFactory;

        AnnotationRegistry::registerFile(__DIR__ . '/Annotation/BindEntity.php');
    }

    public function createEntityManager()
    {
        if ($this->entityManager === null) {
            $this->entityManager = $this->createEntityManagerInternal();
        }

        return $this->entityManager;
    }

    protected function createEntityManagerInternal()
    {
        Type::overrideType('date', CarbonDateType::class);
        Type::overrideType('time', CarbonTimeType::class);
        Type::overrideType('datetime', CarbonDateTimeType::class);
        Type::overrideType('datetimetz', CarbonDateTimeTzType::class);

        return EntityManager::create($this->connectionFactory->createConnection(), $this->configurationFactory->createConfiguration(), $this->eventManager);
    }
}
