<?php
/**
 * This file is part of the Small Neat Box Framework
 * Copyright (c) 2011-2012 Small Neat Box Ltd.
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

namespace snb\http;

//==============================
// RequestHeaders
// Extracts all the http requests headers from the _SERVER array
//==============================
class RequestHeaders extends RequestParams
{
    //==============================
    // __construct
    // Extracts all the http headers from the _SERVER array
    //==============================
    public function __construct($all = array())
    {
        $headers = array();

        // all should be the contents of $_SERVER
        foreach ($all as $key => $value) {
            // All the request headers start with HTTP_ in the _SERVER array
            if (strpos($key, 'HTTP_') === 0) {
                // Convert to a standard notation. X_REQUESTED_WITH => X-Requested-With
                $name = $this->cleanNames(substr($key, 5));
                $headers[$name] = $value;
            } elseif (in_array($key, array('CONTENT_LENGTH', 'CONTENT_MD5', 'CONTENT_TYPE'))) {
                // Except for the ones that don't start HTTP ffs
                $headers[$this->cleanNames($key)] = $value;
            }
        }

        // Look for auth user and password while we are here...
        if (isset($all['PHP_AUTH_USER'])) {
            $password = '';
            if (isset($all['PHP_AUTH_PW'])) {
                $password = $all['PHP_AUTH_PW'];
            }

            $headers['Auth-User'] = $all['PHP_AUTH_USER'];
            $headers['Auth-Password'] = $password;
        }

        // Pass this on to the base class - we are down to just the headers now
        // with all the other crap removed.
        parent::__construct($headers);
    }



    //==============================
    // cleanNames
    // Cleans up the value names to be more consistent with standard notation
    //==============================
    protected function cleanNames($name)
    {
        // Convert to a standard notation. X_REQUESTED_WITH => X-Requested-With
            // convert to lower case
            // replace _ with space
            // capitalise each word
            // join words with -
        return str_replace(' ', '-', ucwords(str_replace('_', ' ', strtolower($name))));
    }

}
