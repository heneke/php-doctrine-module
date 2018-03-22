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

trait EntityManagerArrayConfiguration
{
    private static $KEY_DRIVER = 'driver';
    private static $KEY_UNITS = 'units';
    private static $KEY_PROXY_DIR = 'proxy_dir';
    private static $KEY_DEV_MODE = 'dev_mode';
    private static $KEY_SIMPLE_READER = 'simple_reader';

    public function getMetadataDriver()
    {
        if (!isset($this->getValues()[self::$KEY_DRIVER])) {
            throw new InvalidConfigurationException('Metadata driver required!');
        }

        return $this->getValues()[self::$KEY_DRIVER];
    }

    public function getPersistenceUnits()
    {
        if (!isset($this->getValues()[self::$KEY_UNITS])) {
            throw new InvalidConfigurationException('Persistence units required!');
        }

        return (array) $this->getValues()[self::$KEY_UNITS];
    }

    public function getProxyDir()
    {
        return isset($this->getValues()[self::$KEY_PROXY_DIR]) ? $this->getValues()[self::$KEY_PROXY_DIR] : null;
    }

    public function isDevMode()
    {
        return isset($this->getValues()[self::$KEY_DEV_MODE]) ? $this->getValues()[self::$KEY_DEV_MODE] : false;
    }

    public function isUseSimpleAnnotationReader()
    {
        return isset($this->getValues()[self::$KEY_SIMPLE_READER]) ? $this->getValues()[self::$KEY_SIMPLE_READER] : false;
    }
}
