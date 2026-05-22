<?php

/**
 * The goal of this file is to allow developers a location
 * where they can overwrite core procedural functions and
 * replace them with their own. This file is loaded during
 * the bootstrap process and is called during the framework's
 * execution.
 *
 * This can be looked at as a `master helper` file that is
 * loaded early on, and may also contain additional functions
 * that you'd like to use throughout your entire application
 *
 * @see: https://codeigniter.com/user_guide/extending/common.html
 */

if (! function_exists('bool_val')) {
    /**
     * Normalize a boolean-like value from any source (Postgres, MySQL, form input).
     *
     * Postgres BOOLEAN columns return as the strings 't'/'f' through PDO, while
     * MySQL TINYINT(1) returns as '0'/'1'. A naive (bool) cast on 'f' yields true
     * because non-empty strings are truthy in PHP, which has caused authorization
     * bypass bugs in this project. Use this helper anywhere a DB-backed boolean
     * may be evaluated.
     *
     * Recognized truthy: true, 1, '1', 't', 'true', 'y', 'yes', 'on'.
     * Recognized falsy:  false, 0, '0', 'f', 'false', 'n', 'no', 'off', '', null.
     */
    function bool_val(mixed $value): bool
    {
        if (is_bool($value)) {
            return $value;
        }

        if ($value === null) {
            return false;
        }

        if (is_int($value) || is_float($value)) {
            return (int) $value !== 0;
        }

        if (is_string($value)) {
            $normalized = strtolower(trim($value));

            return in_array($normalized, ['1', 't', 'true', 'y', 'yes', 'on'], true);
        }

        return (bool) $value;
    }
}
