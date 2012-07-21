<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
/*
Copyright (c) 2004-2012 Fabien Potencier

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is furnished
to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
THE SOFTWARE.
*/

namespace snb\errors;

/**
 * ErrorHandler.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class ErrorHandler
{
    private $levels = array(
        E_ERROR				=> 'Error',
        E_PARSE				=> 'Parse Error',
        E_WARNING           => 'Warning',
        E_NOTICE            => 'Notice',
        E_USER_ERROR        => 'User Error',
        E_USER_WARNING      => 'User Warning',
        E_USER_NOTICE       => 'User Notice',
        E_STRICT            => 'Runtime Notice',
        E_RECOVERABLE_ERROR => 'Catchable Fatal Error',
        E_DEPRECATED        => 'Deprecated',
        E_USER_DEPRECATED   => 'User Deprecated',
    );

    private $level;
    private $exceptionHandler;

    /**
     * Register the error handler.
     *
     * @param integer $level The level at which the conversion to Exception is done (null to use the error_reporting() value and 0 to disable)
     *
     * @return The registered error handler
     */
    public static function register(ExceptionHandler $exceptionHandler, $level = null)
    {
        $handler = new static();
        $handler->exceptionHandler = $exceptionHandler;
        $handler->setLevel($level);

        set_error_handler(array($handler, 'handleError'));
        register_shutdown_function(array($handler, 'handleShutdown'));

        return $handler;
    }

    public function setLevel($level)
    {
        $this->level = null === $level ? error_reporting() : $level;
    }

    /**
     * @throws \ErrorException When error_reporting returns error
     */
    public function handleError($level, $message, $file, $line, $context)
    {
        if (0 === $this->level) {
            return false;
        }

        if (error_reporting() & $level && $this->level & $level) {
            throw new \ErrorException(sprintf('%s: %s in %s line %d', isset($this->levels[$level]) ? $this->levels[$level] : $level, $message, $file, $line));
        }

        return false;
    }

    /**
     * Handles shutdown errors
     */
    public function handleShutdown()
    {
        // See if there was an error
        $error = error_get_last();
        if ($error == NULL) {
            return;
        }

        // ensure the error type is set
        if (!isset($error['type'])) {
            return;
        }

        // we are only interested in Fatal Errors
        if ($error['type'] != E_ERROR) {
            return;
        }

        // call the exception handler to handle the error
        $this->exceptionHandler->handle(new \ErrorException(sprintf('%s: %s in %s line %d',
                $this->levels[E_ERROR],
                $error['message'],
                $error['file'],
                $error['line'])));
    }
}
