<?php
/*
 * This file is part of John Koniges' Select class
 * https://github.com/Venar/select
 *
 * Copyright (c) 2015 John J. Koniges
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace select;

/**
 * Param is just an object to better hold data than an array if manipulation is later needed
 */
class Param
{
    public $type;
    public $value;
    public $placeholder;

    public function __construct($value, $type = \PDO::PARAM_STR)
    {
        $this->value = $value;
        $this->type = $type;
    }
}
