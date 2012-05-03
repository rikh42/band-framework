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
 * Essentially, we create the migration table that the migration state is stored in
 */
class CreateTables extends DbMigration
{
	public function up()
	{
		// Create the settings table
		$sql = "
			CREATE TABLE migrations (
				area varchar(128) NOT NULL,
				version int(11) NOT NULL,
				PRIMARY KEY (area)
			) DEFAULT CHARSET=utf8
		";
		$this->db->query($sql);

		// Insert the database version setting into it
		$sql = "INSERT INTO migrations(area, version) VALUES ('snb', 0)";
		$this->db->query($sql);
	}


	public function down()
	{
		// Drop the settings table
		$this->db->query("DROP TABLE migrations");
	}
}