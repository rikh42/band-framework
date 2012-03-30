<?php
/**
 * This file is part of the Small Neat Box Framework
 * Copyright (c) 2011-2012 Small Neat Box Ltd.
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */


namespace snb\http;

use snb\http\SessionStorageInterface;
use snb\core\Database;




//==============================
// Request
// Wraps up all the information about the current http request
//==============================
class SessionStorageDb extends SessionStorage
{
	protected $database;

	//==============================
	// __construct
	//==============================
	public function __construct(Database $database)
	{
		parent::__construct();

		// we will need access to the database to actually read and write our session data
		$this->database = $database;
	}



	//==============================
	// start
	// Starts a session
	//==============================
	public function start()
	{
		// If we have already started the session, don't do it again
		if ($this->started)
			return;

		// Use our own session handlers
		session_set_save_handler(
			array($this, 'open'),
			array($this, 'close'),
			array($this, 'read'),
			array($this, 'write'),
			array($this, 'destroy'),
			array($this, 'garbageCollect')
		);

		// on to the normal behaviour
		parent::start();
	}




	//==============================
	// open
	// Called by PHP when the session is first opened
	//==============================
	public function open($path, $name)
	{
		return true;
	}




	//==============================
	// close
	// Called by PHP when the session finally closed
	//==============================
	public function close()
	{
		return true;
	}




	//==============================
	// read
	// called by PHP to read the session data. All the data is read in one go
	//==============================
	public function read($sessionID)
	{
		// try and find the session data in the database
		$sql = "SELECT sData FROM sessions WHERE sSessionId=:sessionid LIMIT 1";
		$param = array('text:sessionid' => $sessionID);
		$data = $this->database->one($sql, $param);

		// Was the data there?
		if ($data !== null)
		{
			$decode = base64_decode($data);
			return $decode;
		}

		// nope, no data, so start a new session and return an empty data string
		$this->startNewSession($sessionID);
		return '';
	}



	//==============================
	// write
	// called by PHP to write all the session data to the database
	//==============================
	public function write($sessionID, $data)
	{
		// Build the query to update the session in the DB
		$sql = "UPDATE sessions SET sData=:data, iLastTouched=:time WHERE sSessionId=:sessionid LIMIT 1";
		$param = array(
			'text:sessionid' => $sessionID,
			'text:data' => base64_encode($data),
			'int:time' => time()
		);

		// try and do it
		$updateCount = $this->database->query($sql, $param);
		if ($updateCount==0)
		{
			// if we failed, it is because the session was not in the db, so add it
			$this->startNewSession($sessionID, $data);
		}

		// yay
		return true;
	}




	//==============================
	// destroy
	// called by PHP to a single session and all its associated data
	//==============================
	public function destroy($sessionID)
	{
		// Build the query to update the session in the DB
		$sql = "DELETE FROM sessions WHERE sSessionId=:sessionid LIMIT 1";
		$param = array('text:sessionid' => $sessionID);

		// try and do it
		$this->database->query($sql, $param);
		return true;
	}




	//==============================
	// garbageCollect
	// called by PHP occasionally to empty out old
	// dead entries from the session table
	//==============================
	public function garbageCollect($maxLifetime)
	{
		// Build the query to update the session in the DB
		$sql = "DELETE FROM sessions WHERE iLastTouched < :killtime";
		$param = array('int:killtime' => time() - $maxLifetime);

		// try and do it
		$this->database->query($sql, $param);
		return true;
	}




	//==============================
	// startNewSession
	// support function to start a new session
	// by inserting an row in teh database
	//==============================
	protected function startNewSession($sessionID, $data='')
	{
		// try and find the session data in the database
		$sql = "INSERT INTO sessions (sSessionId, sData, iLastTouched) VALUES (:id, :data, :time)";
		$param = array(
			'text:id' => $sessionID,
			'text:data' => base64_encode($data),
			'int:time' => time()
		);
		$this->database->query($sql, $param);
		return true;
	}
}
