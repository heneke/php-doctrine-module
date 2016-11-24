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

use HHIT\Doctrine\Migrations\Contracts\MigrationsConfigurationSource;

trait MigrationsArrayConfiguration
{
    private static $KEY_MODE = 'mode';
    private static $KEY_COLUMN = 'column';
    private static $KEY_NAMESPACE = 'namespace';
    private static $KEY_TABLE = 'table';
    private static $KEY_DIRECTORY = 'directory';
    private static $KEY_PLATFORM_DEPENDENT = 'platform_dependent';

    public function getColumnName()
    {
        return isset($this->getValues()[self::$KEY_COLUMN]) ? $this->getValues()[self::$KEY_COLUMN] : 'version';
    }

    public function getOutputDirectory()
    {
        return isset($this->getValues()[self::$KEY_DIRECTORY]) ? $this->getValues()[self::$KEY_DIRECTORY] : getcwd();
    }

    public function getMode()
    {
        return isset($this->getValues()[self::$KEY_MODE]) ? $this->getValues()[self::$KEY_MODE] : MigrationsConfigurationSource::MODE_DEFAULT;
    }

    public function getNamespace()
    {
        return isset($this->getValues()[self::$KEY_NAMESPACE]) ? $this->getValues()[self::$KEY_NAMESPACE] : 'Migrations';
    }

    public function getTableName()
    {
        return isset($this->getValues()[self::$KEY_TABLE]) ? $this->getValues()[self::$KEY_TABLE] : 'doctrine_migrations_version';
    }

    public function isPlatformDependent()
    {
        return isset($this->getValues()[self::$KEY_PLATFORM_DEPENDENT]) ? $this->getValues()[self::$KEY_PLATFORM_DEPENDENT] : false;
    }
}
