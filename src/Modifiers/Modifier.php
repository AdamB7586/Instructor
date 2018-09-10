<?php

namespace Instructor\Modifiers;

class Modifier {
    /**
     * Set value to null if value is empty
     * @param mixed $variable This should be the variable you are checking if it is empty 
     * @return mixed Returns either NULL or the original variable
     */
    public static function setNullOnEmpty($variable) {
        if(empty(trim($variable))) {
            return NULL;
        }
        return $variable;
    }
}
