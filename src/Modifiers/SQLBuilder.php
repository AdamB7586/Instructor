<?php

namespace Instructor\Modifiers;

use DBAL\Modifiers\SafeString;
use DBAL\Modifiers\Operators;

class SQLBuilder
{
    
    public static $values = [];
    
    /**
     * Returns any additional SQL string
     * @param array $additional This should be any additional info to query on
     * @return string|false Returns the additional SQL string
     */
    public static function createAdditionalString($additional)
    {
        $additionalItems = [];
        if (is_array($additional) && count($additional) >= 1) {
            foreach ($additional as $key => $item) {
                $additionalItems[] = self::formatValues($key, $item);
            }
            return ' AND '.implode(' AND ', $additionalItems);
        }
        return false;
    }
    
    /**
     * Format the where queries and set the prepared values
     * @param string $field This should be the field name in the database
     * @param mixed $value This should be the value which should either be a string or an array if it contains an operator
     * @return string This should be the string to add to the SQL query
     */
    protected static function formatValues($field, $value)
    {
        if (!is_array($value) && Operators::isOperatorValid($value) && !Operators::isOperatorPrepared($value)) {
            return sprintf("`%s` %s", SafeString::makeSafe($field), Operators::getOperatorFormat($value));
        } elseif (is_array($value)) {
            $keys = [];
            if (!is_array(array_values($value)[0])) {
                self::$values[':'.strtoupper(SafeString::makeSafe($field))] = (isset($value[1]) ? $value[1] : array_values($value)[0]);
                $operator = (isset($value[0]) ? $value[0] : key($value));
            } else {
                foreach (array_values($value)[0] as $op => $array_value) {
                    self::$values[':'.strtoupper(SafeString::makeSafe($field))] = $array_value;
                    $keys[] = '?';
                }
                $operator = key($value);
            }
            return str_replace('?', ':'.strtoupper(SafeString::makeSafe($field)), sprintf("`%s` %s", SafeString::makeSafe($field), sprintf(Operators::getOperatorFormat($operator), implode($keys, ', '))));
        }
        self::$values[':'.strtoupper(SafeString::makeSafe($field))] = $value;
        return sprintf("`%s` = :".strtoupper(SafeString::makeSafe($field)), SafeString::makeSafe($field));
    }
}
