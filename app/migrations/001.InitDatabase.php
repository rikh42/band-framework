<?php
/**
 * This file is part of the Small Neat Box Framework
 * Copyright (c) 2011-2012 Small Neat Box Ltd.
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */


namespace migrations;
use snb\core\DbMigration;


/**
 * Migrates from version 0 to version 1.
 * Essentially, we create the settings table that the migration state is stored in
 */
class InitDatabase extends DbMigration
{
	public function up()
	{
		// Create the settings table
		$sql = "
			CREATE TABLE settings (
				name char(200) NOT NULL,
  				value text NOT NULL,
  				PRIMARY KEY (name)
			) DEFAULT CHARSET=utf8
		";
		$this->db->query($sql);

		// Create the tokens table
		$sql = "
			CREATE TABLE tokens (
				id int(11) NOT NULL AUTO_INCREMENT,
  				user_id int(11) NOT NULL,
  				series char(16) NOT NULL,
  				token char(16) NOT NULL,
  				expires int(11) NOT NULL,
  				PRIMARY KEY(id),
  				KEY expires (expires),
  				KEY everything (user_id,series,token,expires)
			) DEFAULT CHARSET = utf8;
		";
		$this->db->query($sql);

		// Create the user info table
		$sql = "
			CREATE TABLE userinfo (
				id int(11) NOT NULL AUTO_INCREMENT,
  				name char(128) NOT NULL,
  				hash char(60) NOT NULL,
  				PRIMARY KEY(id)
			) DEFAULT CHARSET = utf8;
		";
		$this->db->query($sql);
	}


	public function down()
	{
		// Drop the tables we created in up()
		$this->db->query("DROP TABLE userinfo");
		$this->db->query("DROP TABLE tokens");
		$this->db->query("DROP TABLE settings");
	}
}