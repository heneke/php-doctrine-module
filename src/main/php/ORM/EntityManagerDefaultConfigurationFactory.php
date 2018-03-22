<?php

/*
 * The MIT License (MIT)
 *
 * Copyright (c) 2018 Hendrik Heneke
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

use Doctrine\Common\Cache\Cache;
use Doctrine\ORM\Configuration;
use Doctrine\ORM\Tools\Setup;
use HHIT\Doctrine\ORM\Contracts\EntityManagerConfigurationFactory;
use HHIT\Doctrine\ORM\Contracts\EntityManagerConfigurationSource;

class EntityManagerDefaultConfigurationFactory implements EntityManagerConfigurationFactory
{
    /**
     * @var EntityManagerConfigurationSource
     */
    private $source;

    /**
     * @var Cache
     */
    private $cache;

    /**
     * @var Configuration
     */
    private $configuration;

    public function __construct(EntityManagerConfigurationSource $source, Cache $cache = null)
    {
        $this->source = $source;
        $this->cache = $cache;
    }

    public function createConfiguration()
    {
        if ($this->configuration === null) {
            $this->configuration = $this->createConfigurationInternal();
        }

        return $this->configuration;
    }

    protected function createConfigurationInternal()
    {
        switch ($this->source->getMetadataDriver()) {
            case EntityManagerConfigurationSource::METADATA_DRIVER_ANNOTATION:
                return $this->createAnnotationConfig();
            case EntityManagerConfigurationSource::METADATA_DRIVER_YAML:
                return $this->createYamlConfig();
            case EntityManagerConfigurationSource::METADATA_DRIVER_XML:
                return $this->createXmlConfig();
            default:
                throw new InvalidConfigurationException("Metadata driver {$this->source->getMetadataDriver()} is not supported!");
        }
    }

    protected function createAnnotationConfig()
    {
        return Setup::createAnnotationMetadataConfiguration($this->getMappingDirectories(), $this->source->isDevMode(), $this->source->getProxyDir(), $this->cache, $this->source->isUseSimpleAnnotationReader());
    }

    protected function createYamlConfig()
    {
        return Setup::createYAMLMetadataConfiguration($this->getMappingDirectories(), $this->source->isDevMode(), $this->source->getProxyDir(), $this->cache);
    }

    protected function createXmlConfig()
    {
        return Setup::createXMLMetadataConfiguration($this->getMappingDirectories(), $this->source->isDevMode(), $this->source->getProxyDir(), $this->cache);
    }

    protected function getMappingDirectories()
    {
        $paths = [];
        foreach ($this->source->getPersistenceUnits() as $persistenceUnit) {
            $persistenceUnit = new $persistenceUnit();
            $paths = array_merge($paths, (array) $persistenceUnit->getMappingDirectories());
        }

        return $paths;
    }
}
