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

namespace HHIT\Doctrine\DBAL;

use HHIT\Doctrine\DBAL\Contracts\DBALConfigurationSource;

class DBALConsoleHelper
{
    public static function createConnection($fileName = 'doctrine.config.php', $subkey = 'dbal')
    {
        return self::createConnectionInternal(self::createSource($fileName, $subkey));
    }

    public static function createConnectionFactory($fileName = 'doctrine.config.php', $subkey = 'dbal')
    {
        return self::createConnectionFactoryInternal(self::createSource($fileName, $subkey));
    }

    private static function createConnectionInternal(DBALConfigurationSource $configurationSource)
    {
        return self::createConnectionFactoryInternal($configurationSource)->createConnection();
    }

    private static function createConnectionFactoryInternal(DBALConfigurationSource $source)
    {
        return new DBALDefaultConnectionFactory(new DBALDefaultConfigurationFactory($source));
    }

    private static function createSource($fileName, $key)
    {
        $files = [getcwd().DIRECTORY_SEPARATOR.$fileName, getcwd().DIRECTORY_SEPARATOR.'config'.DIRECTORY_SEPARATOR.$fileName];
        foreach ($files as $file) {
            if (file_exists($file)) {
                return new DBALFileConfigurationSource($file, $key);
            }
        }

        throw new \RuntimeException('Please configure DBAL with '.implode(' or ', $files).'!');
    }
}
