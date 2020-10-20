<?php

namespace Instructor\Modifiers;

class Format
{
    
    /**
     * Return only the first name for the instructor
     * @param string $name The full name for the instructor
     * @return string Will return only the first name
     */
    public static function firstname($name)
    {
        $names = explode(' ', $name);
        return $names[0];
    }
    
    /**
     * Format the postcode string for viewing
     * @param string $postcodes The list of postcodes that the instructor covers
     * @return string A formatted list will be returned to make it more easily readable
     */
    public static function instPostcodes($postcodes)
    {
        return str_replace(',', ', ', trim($postcodes, ','));
    }
    
    /**
     * Returns only the first part of a given postcode
     * @param string $postcode This should be the give postcode to convert to the small postcode
     * @param boolean $alpha If you only want the alpha characters and not any numeric set this to true
     * @return string The small postcode will be returned
     */
    public static function smallPostcode($postcode, $alpha = false)
    {
        $pcode = self::replaceIncorrectNumbers($postcode);
        $length = strlen($pcode);

        if ($length >= 5) {
            $smallpcode = substr($pcode, 0, $length - 3);
        } else {
            $smallpcode = $pcode;
        }
        if ($alpha !== false) {
            $smallpcode = preg_replace('/[^A-Za-z_]/', '', $smallpcode);
        }

        return strtoupper($smallpcode);
    }
    
    /**
     * Replace special characters with the corresponding number on the keyboard
     * @param string $string This should be the string where incorrect values will be replaced
     * @return string The correctly formatted string will be returned
     */
    public static function replaceIncorrectNumbers($string)
    {
        $characters = ['!', '"', '£', '$', '%', '^', '&', '*', '(', ')', ' '];
        $numbers = [1, 2, 3, 4, 5, 6, 7, 8, 9, 0, ''];
        return str_replace($characters, $numbers, trim($string));
    }
    
    /**
     * Converts a list string (comma separated) in
     * @param string $list This should be a list in a comma separated string
     * @return array Will return an array of values
     */
    public static function getListArray($list)
    {
        return explode(',', str_replace(' ', '', trim(trim($list), ',')));
    }
}
