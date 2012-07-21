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

use snb\http\Response;
use snb\errors\FlattenException;
use snb\errors\FlattenExceptionFormatter;

/**
 * ExceptionHandler converts an exception to a Response object.
 *
 * It is mostly useful in debug mode to replace the default PHP/XDebug
 * output with something prettier and more useful.
 *
 * As this class is mainly used during Kernel boot, where nothing is yet
 * available, the Response content is always HTML.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class ExceptionHandler
{
    /**
     * Register the exception handler.
     *
     * @return ExceptionHandler The registered exception handler
     */
    public static function register()
    {
        $handler = new static();
        set_exception_handler(array($handler, 'handle'));

        return $handler;
    }

    /**
     * Sends a Response for the given Exception.
     *
     * @param \Exception $exception An \Exception instance
     */
    public function handle(\Exception $exception)
    {
        $this->createResponse($exception)->send();
    }

    /**
     * Creates the error Response associated with the given Exception.
     *
     * @param \Exception $exception An \Exception instance
     *
     * @return Response A Response instance
     */
    public function createResponse(\Exception $exception)
    {
        // defaults
        $content = '';
        $title = '';

        // try and get decent values
        try {
            $code = 500;
            $title = 'We\'re sorry, but it looks like something went wrong.';
            $exception = FlattenException::create($exception);
            $content = FlattenExceptionFormatter::formatException($exception);
        } catch (\Exception $e) {
            $title = 'We\'re sorry, but it looks like something went wrong.';
        }

        // build a response out of anything we got
        return new Response($this->decorate($content, $title), $code);
    }

    protected function decorate($content, $title)
    {
        return <<<EOF
<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
        <meta name="robots" content="noindex,nofollow" />
        <title>{$title}</title>
        <style>
            /* Copyright (c) 2010, Yahoo! Inc. All rights reserved. Code licensed under the BSD License: http://developer.yahoo.com/yui/license.html */
            html{color:#000;background:#FFF;}body,div,dl,dt,dd,ul,ol,li,h1,h2,h3,h4,h5,h6,pre,code,form,fieldset,legend,input,textarea,p,blockquote,th,td{margin:0;padding:0;}table{border-collapse:collapse;border-spacing:0;}fieldset,img{border:0;}address,caption,cite,code,dfn,em,strong,th,var{font-style:normal;font-weight:normal;}li{list-style:none;}caption,th{text-align:left;}h1,h2,h3,h4,h5,h6{font-size:100%;font-weight:normal;}q:before,q:after{content:'';}abbr,acronym{border:0;font-variant:normal;}sup{vertical-align:text-top;}sub{vertical-align:text-bottom;}input,textarea,select{font-family:inherit;font-size:inherit;font-weight:inherit;}input,textarea,select{*font-size:100%;}legend{color:#000;}

            html { background: #eee; padding: 10px }
            body { font: 11px Verdana, Arial, sans-serif; color: #333 }
            img { border: 0; }
            .clear { clear:both; height:0; font-size:0; line-height:0; }
            .clear_fix:after { display:block; height:0; clear:both; visibility:hidden; }
            .clear_fix { display:inline-block; }
            * html .clear_fix { height:1%; }
            .clear_fix { display:block; }
            #content { width:970px; margin:10px auto; }
            .sf-exceptionreset, .sf-exceptionreset .block { margin: auto }
            .sf-exceptionreset abbr { border-bottom: 1px dotted #000; cursor: help; }
            .sf-exceptionreset p { font-size:14px; line-height:20px; color:#868686; padding-bottom:20px }
            .sf-exceptionreset strong { font-weight:bold; }
            .sf-exceptionreset a { color:#6c6159; }
            .sf-exceptionreset a img { border:none; }
            .sf-exceptionreset a:hover { text-decoration:underline; }
            .sf-exceptionreset em { font-style:italic; }
            .sf-exceptionreset h1, .sf-exceptionreset h2 { font: 20px Georgia, "Times New Roman", Times, serif }
            .sf-exceptionreset h2 span { background-color: #fff; color: #333; padding: 6px; float: left; margin-right: 10px; }
            .sf-exceptionreset .traces li { font-size:12px; padding: 2px 4px; list-style-type:decimal; margin-left:20px; }
            .sf-exceptionreset .block { background-color:#FFFFFF; padding:10px 28px; margin-bottom:20px;
                -webkit-border-bottom-right-radius: 16px;
                -webkit-border-bottom-left-radius: 16px;
                -moz-border-radius-bottomright: 16px;
                -moz-border-radius-bottomleft: 16px;
                border-bottom-right-radius: 16px;
                border-bottom-left-radius: 16px;
                border-bottom:1px solid #ccc;
                border-right:1px solid #ccc;
                border-left:1px solid #ccc;
            }
            .sf-exceptionreset .block_exception { background-color:#ddd; color: #333; padding:20px;
                -webkit-border-top-left-radius: 16px;
                -webkit-border-top-right-radius: 16px;
                -moz-border-radius-topleft: 16px;
                -moz-border-radius-topright: 16px;
                border-top-left-radius: 16px;
                border-top-right-radius: 16px;
                border-top:1px solid #ccc;
                border-right:1px solid #ccc;
                border-left:1px solid #ccc;
            }
            .sf-exceptionreset li a { background:none; color:#868686; text-decoration:none; }
            .sf-exceptionreset li a:hover { background:none; color:#313131; text-decoration:underline; }
            .sf-exceptionreset ol { padding: 10px 0; }
            .sf-exceptionreset h1 { background-color:#FFFFFF; padding: 15px 28px; margin-bottom: 20px;
                -webkit-border-radius: 10px;
                -moz-border-radius: 10px;
                border-radius: 10px;
                border: 1px solid #ccc;
            }
        </style>
    </head>
    <body>
        <div id="content" class="sf-exceptionreset">
            <h1>$title</h1>
            $content
        </div>
    </body>
</html>
EOF;
    }
}
