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

/**
 * How would this work?
 *
 * migrate.php file that is part of app?
 * A folder somewhere, with all the migrations in it
 * 		app/migrations/001.CreateCore.php
 * 		app/migrations/002.AddSettings.php
 *
 * We scan the folder, extracting all the files that match the version.name.php pattern
 * We find out what version we have in the database.
 * We find out what version we want (latest, or specific version number)
 * We load all the classes between what we have and where we want to be,
 * 		calling up() or down() as we go.
 *
 * The logger we use should just echo out to stdout
 * 		Would be nice if if would also write to some log file
 *
 * If up() or down() return false. we should stop.
 *
 * The system depends on a migration table / setting, so that we know what
 * version we are on.
 *
 */

/**
 * Base class of a single migration from one version of the database to another
 */
class DbMigration
{
    protected $db;
    protected $logger;

    public function __construct(DatabaseInterface $db, LoggerInterface $logger)
    {
        $this->db = $db;
        $this->logger = $logger;
    }

    public function up()
    {
    }

    public function down()
    {
    }

    /*
     * public function useTransaction();
     */

}
