<?php
/**
 * This file is part of the Small Neat Box Framework
 * Copyright (c) 2011-2012 Small Neat Box Ltd.
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */


namespace snb\core;
use snb\core\DatabaseInterface;
use snb\logger\LoggerInterface;
use snb\core\ContainerAware;


/**
 * The Migration Service.
 * This is responsible for handling database migrations
 */
class Migrate extends ContainerAware
{
	protected $db;
	protected $logger;


	/**
	 * Get ready to perform a migration
	 * @param DatabaseInterface $db
	 * @param \snb\logger\LoggerInterface $logger
	 */
	public function __construct(DatabaseInterface $db, LoggerInterface $logger)
	{
		$this->db = $db;
		$this->logger = $logger;
	}



	/**
	 * Creates the table with all the migrations in it, if it does not exist already.
	 * This is automatically called by the service container if you create the migration
	 * using that method
	 */
	public function ensureMigrationTable()
	{
		// upgrade the core database (ie, the migrations table) to the latest version
		$this->latest('snb');
	}




	/**
	 * Finds all the areas in the system, defined by all the packages
	 * and migrates them to the latest version
	 */
	public function updateAll()
	{
		// Find all the areas that we are tracking
		$sql = "SELECT area FROM migrations";
		$areas = $this->db->all($sql);
		if ($areas)
		{
			// Update every area to the latest version
			foreach($areas as $item)
			{
				$this->logger->info("Migrating area {$item->area} to latest version");
				$this->latest($item->area);
			}
		}
	}





	/**
	 * Migrates the database up one version for the named area
	 * @param string $area - the name of an area (package name) to upgrade
	 */
	public function up($area='app')
	{
		// go up one version
		$version = $this->getCurrentVersion($area);
		$migrations = $this->getMigrations($area);
		$this->migrateToVersion($version+1, $version, $migrations, $area);
	}



	/**
	 * Go down one version (roll back)
	 * @param string $area - the name of an area (package name) to upgrade
	 */
	public function down($area='app')
	{
		// go down one version
		$version = $this->getCurrentVersion($area);
		$migrations = $this->getMigrations($area);
		$this->migrateToVersion($version-1, $version, $migrations, $area);
	}



	/**
	 * Go up to the latest version
	 * @param string $area - the name of an area (package name) to upgrade
	 */
	public function latest($area='app')
	{
		// go to the latest version
		$version = $this->getCurrentVersion($area);
		$migrations = $this->getMigrations($area);
		$this->migrateToVersion(null, $version, $migrations, $area);
	}




	/**
	 * Go to a specific named version
	 * @param $targetVersion
	 * @param string $area - the name of an area (package name) to upgrade
	 */
	public function version($targetVersion, $area='app')
	{
		// go to the specific named version
		$version = $this->getCurrentVersion($area);
		$migrations = $this->getMigrations($area);
		$this->migrateToVersion($targetVersion, $version, $migrations, $area);
	}




	/**
	 * Migrates the database to the target version from the current version
	 * @param $target - the version we want to go to
	 * @param $current - the version we are on now
	 * @param $migrations - a list of the available migration classes
	 * @param string $area - the name of an area (package name) to upgrade
	 * @return mixed
	 */
	protected function migrateToVersion($target, $current, $migrations, $area)
	{
		// If there are no migrations at all, then there is nothing we can do
		if (empty($migrations))
			return;

		// Find the latest version
		$versions = array_keys($migrations);
		$latest = end($versions);

		// target is null when we want to go to the latest version
		if (($target === null) || ($target > $latest))
			$target = $latest;

		// Any version before version 0, is mapped to version 0
		if ($target < 0)
			$target = 0;

		// if we are already on the target version, we are don
		if ($target == $current)
			return;

		// keep going until we hit a problem or we reach our target version
		while ($current != $target)
		{
			// Are we going up or down
			// When going up, we jump to the next version and execute its up function
			// When going down, we execute the down function on the current version, then change the version number down.
			if ($target > $current)
			{
				$next = $current + 1;
				if (!array_key_exists($next, $migrations))
				{
					$this->logger->error("Missing migration version $next in $area - Aborting");
					return;
				}

				// Execute the next version migrate up function
				if (!$this->migrateMethod($migrations[$next], 'up', $next))
					return;

				// update the version number
				$current++;
			}
			else
			{
				// Make sure the migration version exists
				if (!array_key_exists($current, $migrations))
				{
					$this->logger->error("Missing migration version $current in $area - Aborting");
					return;
				}

				// Go down from the current version to the one below
				$this->migrateMethod($migrations[$current], 'down', $current-1);

				// Update the version number
				$current--;
			}

			// write the version number back to the database
			$this->setCurrentVersion($current, $area);
		}
	}




	/**
	 * Perform a single migration using the class at path
	 * @param $path - path to the migration class
	 * @param $method - up or down?
	 * @param $version - the version we are migrating to
	 * @return bool
	 */
	protected function migrateMethod($path, $method, $version)
	{
		$file = basename($path);
		if (preg_match('/^(\d+)\.(\w+)\.php/i', $file, $regs))
		{
			// Include the migration class
			include_once $path;

			// Check that the class is valid
			$class = '\\migrations\\'.$regs[2];
			if (!class_exists($class, false))
			{
				$this->logger->error("Migration Class $class does not exist in $path");
				return false;
			}

			// It needs to have an up and down function
			if ((!is_callable(array($class, 'up'))) || (!is_callable(array($class, 'down'))))
			{
				$this->logger->error("Migration Class $class is missing up() or down()");
				return false;
			}

			// Create an instance of it and call up/down
			$this->logger->info("Migrating to version $version using $class");
			$obj = new $class($this->db, $this->logger);
			if ($obj->$method() === false)
				return false;

			// get rid of the migration again
			$obj = null;
		}
		else
		{
			$this->logger->error("Migration File $path does not match regex pattern");
			return false;
		}

		// yay
		return true;
	}



	/**
	 * Gets the list of possible migrations, in order
	 * @param string $area - the name of an area (package name) to upgrade
	 * @return array
	 */
	protected function getMigrations($area)
	{
		// get the components we need
		$kernel = $this->container->get('kernel');

		// Look up the path to the migrations
		$location = $area.':/migrations';
		$migrationPath = $kernel->findPath($location);
		$migrationPath .= '/*.*.php';

		// Find all the files that look like they might be migrations
		$files = glob($migrationPath);
		if (!$files)
			return array();

		// Now check all the files match our criteria,
		// and build a list of available versions
		$migration = array();
		foreach($files as $filename)
		{
			// Extract the filename from the path name
			$name = basename($filename);

			// Check it matches 123.classname.php
			if (preg_match('/^(\d+)\.(\w+)\.php/i', $name, $regs))
			{
				$version = (int)$regs[1];
				$migration[$version] = $filename;
			}
		}

		// Sort them into version order
		ksort($migration, SORT_NUMERIC);

		// Return the list of migrations
		return $migration;
	}



	/**
	 * Gets the current version of the database, from the database
	 * @param string $area - the name of an area (package name) to upgrade
	 * @return int
	 */
	protected function getCurrentVersion($area)
	{
		// Attempt to find out what version we are on now.
		$sql = "SELECT version FROM migrations WHERE area=:area";
		$args = array('area' => $area);
		$version = $this->db->one($sql, $args);
		if ($version === null)
		{
			// Failed to find the area in the table, so attempt to create
			// an entry for it (at version 0)
			$sql = "INSERT INTO migrations(area, version) VALUES (:area, 0)";
			$this->db->query($sql, $args);

			// That can fail (if the migrations table does not exist for example)
			// but other parts of the system attempt to create that in time.
			$version = 0;
		}

		// Return any version number we found
		return $version;
	}



	/**
	 * Sets the current version of teh database to the value supplied
	 * @param string $area - the name of an area (package name) to upgrade
	 * @param $version
	 */
	protected function setCurrentVersion($version, $area)
	{
		// Attempt to find out what version we are on now.
		$sql = "UPDATE migrations set version=:version WHERE area=:area";
		if ($this->db->query($sql, array('version'=>$version, 'area'=>$area)) != 1)
		{
			$this->logger->warning("Failed to update version to $version in migration area $area");
		}
	}

}