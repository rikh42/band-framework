<?php
/**
 * This file is part of the Small Neat Box Framework
 * Copyright (c) 2011-2012 Small Neat Box Ltd.
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

namespace snb\logger;
use snb\logger\LoggerInterface;

class Logger implements LoggerInterface
{
    const DEBUG = 100;		// useful to know if you were debugging the app
    const INFO = 200;		// Stuff it would be good to know about (eg a login)
    const WARNING = 300;	// something stange is going on
    const ERROR = 400;		// there was some kind of error

    protected $handler;

    public function __construct(HandlerInterface $handler = null)
    {
        $this->handler = $handler;
    }

    /**
     * Adds a single record to the log
     * @param $level
     * @param $message
     * @param $extraData
     * @return mixed
     */
    protected function addRecord($level, $message, $extraData)
    {
        // If there is no handler defined, do nothing
        if ($this->handler==null) {
            return;
        }

        // create the record
        $record = array(
            'message' => (string) $message,
            'extradata' => $extraData,
            'time' => new \DateTime(),
            'level' => $level
        );

        // send it to the handler to process it
        $this->handler->handle($record);
    }

    public function dump()
    {
        echo $this->getLog();
    }

    public function getLog()
    {
        if ($this->handler == null) {
            return '';
        }

        return $this->handler->get();
    }

    public function getHtmlLog()
    {
        if ($this->handler == null) {
            return '';
        }

        return $this->handler->getHtml();
    }

    /**
     * Adds a debug level message to the log
     * @param $message
     * @param null $extraData
     */
    public function debug($message, $extraData = null)
    {
        $this->addRecord(self::DEBUG, $message, $extraData);
    }

    /**
     * Log an info level message (ie, something that we want to know about
     * but is normal behaviour, such as a user logging in)
     * @param $message
     * @param null $extraData
     */
    public function info($message, $extraData = null)
    {
        $this->addRecord(self::INFO, $message, $extraData);
    }

    /**
     * Logs a warning message. Normally used when something has gone wrong,
     * but we can deal with it
     * @param $message
     * @param null $extraData
     */
    public function warning($message, $extraData = null)
    {
        $this->addRecord(self::WARNING, $message, $extraData);
    }

    /**
     * Log an error message. This should be used when something serious has gone
     * wrong that is hard to recover from, like key config files missing, database
     * is down etc.
     * @param $message
     * @param null $extraData
     */
    public function error($message, $extraData = null)
    {
        $this->addRecord(self::ERROR, $message, $extraData);
    }

    /**
     * Call to log the memory usage at the time of the call. The message
     * should provide some indication of what was going on and where you are calling from
     * @param $message
     */
    public function logMemory($message)
    {
        // Find out the memory usage at the moment
        $data = array(
            'peak' => memory_get_peak_usage(true),
            'current' => memory_get_usage(true)
        );

        $this->addRecord(self::DEBUG, $message, $data);
    }

    /**
     * Called to log a Query to the database. We store the query, arguments and
     * timing data in the log. At display time we will also attempt to make calls
     * to EXPLAIN, in order to extract extra data about the query.
     * @param $message
     * @param $sql
     * @param $args
     * @param $queryTime
     */
    public function logQuery($message, $sql, $args, $queryTime)
    {
        $data = array(
            'sqlQuery' => $sql,
            'args' => $args,
            'time' => $queryTime
        );

        $this->addRecord(self::DEBUG, $message, $data);
    }

    /**
     * Logs the time at the point of the call. The message should include enough
     * information to figure out where the call was made. Useful for find slow
     * spots in the code.
     * @param $message
     */
    public function logTime($message)
    {
        $data = array(
            'time' => microtime(true)
        );

        $this->addRecord(self::DEBUG, $message, $data);
    }
}
