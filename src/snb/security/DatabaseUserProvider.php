<?php
/**
 * This file is part of the Small Neat Box Framework
 * Copyright (c) 2011-2012 Small Neat Box Ltd.
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

namespace snb\security;
use snb\security\UserProviderInterface;
use snb\core\DatabaseInterface;

/**
 * Provides access to user info via a database.
 * This class expects certain data to be available in a userinfo table.
 * the min spec for this table is...
    CREATE TABLE userinfo (
        id int(11) NOT NULL AUTO_INCREMENT,
        name char(128) NOT NULL,
        hash char(60) NOT NULL,
        PRIMARY KEY(id)
    ) DEFAULT CHARSET = utf8;
 * Though it is welcome to have more than this
 */
class DatabaseUserProvider implements UserProviderInterface
{
    protected $db;
    protected $userName;
    protected $userId;
    protected $hash;

    public function __construct(DatabaseInterface $database)
    {
        $this->db = $database;
        $this->reset();
    }

    /**
     * Resets all the data in the provider
     */
    protected function reset()
    {
        $this->userId = 0;
        $this->userName = '';
        $this->hash = '';
    }

    /**
     * Loads the user info for the named user
     * @param $username
     * @return bool
     */
    public function loadFromUserName($username)
    {
        // reset everything
        $this->reset();

        // Try and load the data
        $sql = "SELECT id, hash FROM userinfo WHERE name=:username";
        $params = array('text:username' => $username);
        $user = $this->db->row($sql, $params);
        if (!$user) {
            return false;
        }

        // remember the data
        $this->userName = $username;
        $this->userId = $user->id;
        $this->hash = $user->hash;

        return true;
    }

    /**
     * Returns an int that is unique to the user
     * @abstract
     * @return int
     */
    public function getUserId()
    {
        return $this->userId;
    }

    /**
     * Returns the user name (normally would be the same as passed to loadFromUserName)
     * @abstract
     * @return string
     */
    public function getUserName()
    {
        return $this->userName;
    }

    /**
     * Returns the password hash associated with the user
     * @abstract
     * @return string
     */
    public function getUserHash()
    {
        return $this->hash;
    }
}
