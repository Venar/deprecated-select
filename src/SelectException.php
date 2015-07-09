<?php
/**
 * Created by PhpStorm.
 * User: jkonige2
 * Date: 7/9/2015
 * Time: 9:02 AM
 */

namespace select;


class SelectException extends \Exception {
    // custom string representation of object
    public function __toString() {
        return __CLASS__ . ": [{$this->code}]: {$this->message}\n";
    }
}
