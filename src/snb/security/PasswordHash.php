<?php
/**
 * This file is part of the Small Neat Box Framework
 * Copyright (c) 2011-2012 Small Neat Box Ltd.
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 */

namespace snb\security;

//==============================
// PasswordHash
// Functions to support hashing and processing passwords
// uses the blowfish crypt function to provide a high workload password hashing solution
//==============================
class PasswordHash
{

    //==============================
    // generatePasswordHash
    // Create the hash value to be stored in the db for a new password
    // Note that this function generates a random salt to go with combine into the hash
    // returns a 60 character string that contains the hash and the salt together
    //==============================
    public function generatePasswordHash($password)
    {
        // generate a random salt for the password
        $salt = $this->generateBlowFishSalt(10);

        // build the hash
        return crypt($password, $salt);
    }

    //==============================
    // ValidatePassword
    // The password is encrypted using the salt in the hash and the final
    // result is compared to the hash.
    // returns true is the password is a match, or false if it is bad
    //==============================
    public function ValidatePassword($password, $hash)
    {
        // make sure we have a valid hash from the database
        if (strlen($hash) != 60) {
            return false;
        }

        // Make sure our hash looks like a valid blowfish hash
        if (!preg_match('%^\$2a\$([0-9]{2})\$[0-9a-zA-Z./]{53}%m', $hash, $regs)) {
            return false;
        }

        // extract the workload figure and stop it being crazy high as a DOS attack
        $workload = (int) $regs[1];
        if ($workload > 20) {
            return false;
        }

        // Finally, extract the salt part of the hash and attempt to validate the password
        $salt = substr($hash, 0, 29);
        $newHash = crypt($password, $salt);

        // Is it a match?
        return ($newHash == $hash);
    }

    //==============================
    // generateRandomToken
    // Generates a random sequence of letters, numbers . and /
    //==============================
    public function generateRandomToken($length)
    {
        return $this->generateRandomSalt($length);
    }

    /**
     * Returns a token that contains a smaller set of characters
     * from our normal random algo. However, the tokens have fewer
     * parsing issues and are simpler to use in some cases.
     * @param $length
     * @return string
     */
    public function generateSimpleToken($length)
    {
        $key = '0123456789abcdefghijklmnopqrstuvwxyz';

        return $this->generateRandomCharacters($key, $length);
    }

    //==============================
    // generateBlowFishSalt
    // Generates a valid Blowfish salt string.
    // Work indicates the work level to build into the hash (4-31)
    // Values around 10 or less are fast.
    // Over 10 and it starts to take a noticeable delay (15 takes a few seconds on my dev box).
    // Higher workloads make the password checking more CPU intensive, slowing up brute force attacks
    // but add to the load of the server.
    //==============================
    protected function generateBlowFishSalt($work)
    {
        $work = (int) $work;
        if (($work<4) || ($work>31)) {
            $work = 8;
        }

        $salt = sprintf("$2a$%02d$", $work);
        $salt .= $this->generateRandomSalt(22);

        return $salt;
    }

    //==============================
    // generateRandomSalt
    // Generates a string of random characters from a valid subset
    //==============================
    protected function generateRandomSalt($length)
    {
        // This is the subset of characters to generate the salt with
        // This is all the characters allowed by the Blowfish algo that we use.
        $key = './0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';

        return $this->generateRandomCharacters($key, $length);
    }

    /**
     * Produce a list of random characters, using the character set given
     * @param $key
     * @param $length
     * @return string
     */
    protected function generateRandomCharacters($key, $length)
    {
        // Get the length of the character set
        $keyLen = strlen($key);
        $salt = '';

        // Go get some random numbers
        $rnd = $this->getRandomValues($length, $keyLen);

        // finally, build the string by looking up random characters in the key
        for ($i=0; $i<$length; $i++) {
            $salt .= $key[$rnd[$i]];
        }

        // return it
        return $salt;
    }

    //==============================
    // getRandomValues
    // Generates an array of random numbers from 0 to ($range-1)
    //==============================
    protected function getRandomValues($count, $range)
    {
        $bytes = '';

        // try and read from /dev/urandom first
        // it's slow, but very random.
        // We don't use /dev/random, as that can block until
        // more randomness turns on the system.
        if (is_readable('/dev/urandom')) {
            $randHand = @fopen('/dev/urandom', 'rb');
            if ($randHand) {
                $bytes = fread($randHand, $count);
                fclose($randHand);
            }
        }

        // See if we got any random bytes from that...
        if (strlen($bytes) < $count) {
            // generate some random values
            $randState = rand() . microtime() . mt_rand();

            // build a binary string
            for ($i = 0; $i < $count; $i += 16) {
                $randState  = md5(mt_rand() . microtime() . $randState);
                $bytes .= md5($randState, true);
            }
            $bytes = substr($bytes, 0, $count);
        }

        // now create an array of value
        $rnd = array();
        for ($i = 0; $i < $count; $i++) {
            // Don't use mod, as that will distort the randomness
            $rnd[] = (int) (ord($bytes[$i]) / 255 * $range);
        }

        return $rnd;
    }
}
