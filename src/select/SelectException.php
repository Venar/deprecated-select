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


class SelectException extends \Exception {
    // custom string representation of object
    public function __toString() {
        return __CLASS__ . ": [{$this->code}]: {$this->message}\n";
    }
}
