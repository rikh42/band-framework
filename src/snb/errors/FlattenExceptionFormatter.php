<?php
// This is a modified version of the Symfony 2 Exception Handler.

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

use snb\errors\FlattenException;

/**
 * Pulled this code out of the exception handler so it can be re-used.
 */
class FlattenExceptionFormatter
{
    public static function formatException(FlattenException $exception)
    {
        $count = count($exception->getAllPrevious());
        $content = '';
        foreach ($exception->toArray() as $position => $e) {
            $ind = $count - $position + 1;
            $total = $count + 1;
            $class = self::abbrClass($e['class']);
            $message = nl2br($e['message']);
            $content .= "<div class=\"block_exception clear_fix\"><h2><span>$ind/$total</span> $class: $message</h2></div><div class=\"block\"><ol class=\"traces list_exception\">";
            foreach ($e['trace'] as $i => $trace) {
                $content .= '<li>';
                if ($trace['function']) {
                    $content .= sprintf('at %s%s%s()', self::abbrClass($trace['class']), $trace['type'], $trace['function']);
                }
                if (isset($trace['file']) && isset($trace['line'])) {
                    $content .= sprintf(' in %s line %s', $trace['file'], $trace['line']);
                }
                $content .= '</li>';
            }

            $content .= '</ol></div>';
        }

        return $content;

    }

    public static function abbrClass($class)
    {
        $parts = explode('\\', $class);

        return sprintf("<abbr title=\"%s\">%s</abbr>", $class, array_pop($parts));
    }
}
