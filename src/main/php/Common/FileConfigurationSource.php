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

namespace HHIT\Doctrine\Common;

abstract class FileConfigurationSource extends ArrayConfigurationSource
{
    /**
     * @var string
     */
    private $file;

    public function __construct($file, $subkey = null)
    {
        if (!$file) {
            throw new \InvalidArgumentException('File required!');
        }
        $this->file = realpath($file);
        if (!$this->file) {
            throw new \InvalidArgumentException("File {$file} does not exist!");
        }
        // @codeCoverageIgnoreStart
        if (!is_readable($this->file)) {
            throw new \InvalidArgumentException("File {$this->file} is not readable!");
        }
        // @codeCoverageIgnoreEnd

        parent::__construct($this->readFile(), $subkey);
    }

    private function readFile()
    {
        $values = require $this->file;
        if (!is_array($values)) {
            throw new \UnexpectedValueException("Configuration in {$this->file} is not an array!");
        }

        return $values;
    }
}
