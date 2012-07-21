<?php
/**
 * This file is part of the Small Neat Box Framework
 * Copyright (c) 2011-2012 Small Neat Box Ltd.
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

namespace snb\http;

use snb\http\RequestParams;
use snb\http\RequestHeaders;
use snb\http\RequestFiles;
use snb\http\SessionStorageInterface;

//==============================
// Request
// Wraps up all the information about the current http request
//==============================
class Request
{
    /**
     * @var RequestParams $get - get request params
     * @var RequestParams $post - posted params
     * @var RequestParams $server - server params
     * @var RequestParams $cookies - The request cookie
     * @var RequestHeaders $headers - request headers
     * @var RequestFiles $files - request files
     * @var SessionStorageInterface $session - the session (if there is one)
     * @var bool $trustProxy - do we trust proxy redirections
     */
    public $get;
    public $post;
    public $server;
    public $cookies;
    public $files;
    public $headers;
    protected $session;
    protected $trustProxy;

    //==============================
    // __construct
    //==============================
    public function __construct(array $get=array(), array $post=array(), array $server=array(), $cookies=array(), array $files=array())
    {
        $this->init($get, $post, $server, $cookies, $files);
    }

    /**
     * @param $get
     * @param $post
     * @param $server
     * @param $cookies
     * @param $files
     */
    protected function init($get, $post, $server, $cookies, $files)
    {
        $this->get = new RequestParams($get);
        $this->post = new RequestParams($post);
        $this->server = new RequestParams($server);
        $this->cookies = new RequestParams($cookies);
        $this->headers = new RequestHeaders($server);
        $this->files = new RequestFiles($files);

        // no session by default.
        $this->session = null;

        // Do we trust proxy connections
        // since we are behind a proxy, then yes
        $this->trustProxy = true;
    }

    /**
     * Create a request object based on the PHP super globals $_GET etc
     * @static
     * @return Request
     */
    public static function createFromGlobals()
    {
        return new static($_GET, $_POST, $_SERVER, $_COOKIE, $_FILES);
    }

    /*
     * this function taken mostly from Symfony 2
     * MIT License.
     * http://symfony.com/doc/current/contributing/code/license.html
     */

    /**
     * Creates a request object that matches your params
     * @static
     * @param $uri
     * @param string $method
     * @param array  $parameters
     * @param array  $server
     * @param array  $cookies
     * @param array  $files
     */
    public static function create($uri, $method='GET', $parameters=array(), $server=array(), $cookies=array(), $files=array())
    {
        // Some defaults for a fake request
        $defaults = array(
            'SERVER_NAME'          => 'localhost',
            'SERVER_PORT'          => 80,
            'HTTP_HOST'            => 'localhost',
            'HTTP_USER_AGENT'      => 'Framework',
            'HTTP_ACCEPT'          => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
            'HTTP_ACCEPT_LANGUAGE' => 'en-us,en;q=0.5',
            'HTTP_ACCEPT_CHARSET'  => 'ISO-8859-1,utf-8;q=0.7,*;q=0.7',
            'REMOTE_ADDR'          => '127.0.0.1',
            'SCRIPT_NAME'          => '',
            'SCRIPT_FILENAME'      => '',
            'SERVER_PROTOCOL'      => 'HTTP/1.1',
            'REQUEST_TIME'         => time(),
        );

        // break up the url passed in
        $components = parse_url($uri);
        if (isset($components['host'])) {
            $defaults['SERVER_NAME'] = $components['host'];
            $defaults['HTTP_HOST'] = $components['host'];
        }

        if (isset($components['scheme'])) {
            if ('https' === $components['scheme']) {
                $defaults['HTTPS'] = 'on';
                $defaults['SERVER_PORT'] = 443;
            }
        }

        if (isset($components['port'])) {
            $defaults['SERVER_PORT'] = $components['port'];
            $defaults['HTTP_HOST'] = $defaults['HTTP_HOST'].':'.$components['port'];
        }

        if (!isset($components['path'])) {
            $components['path'] = '';
        }

        // make sure all the parameters are text
        $stringParams = array();
        foreach ($parameters as $key=>$value) {
            $stringParams[$key] = (string) $value;
        }
        $parameters = $stringParams;

        if (in_array(strtoupper($method), array('POST', 'PUT', 'DELETE'))) {
            $request = $parameters;
            $query = array();
            $defaults['CONTENT_TYPE'] = 'application/x-www-form-urlencoded';
        } else {
            $request = array();
            $query = $parameters;
            if (false !== $pos = strpos($uri, '?')) {
                $qs = substr($uri, $pos + 1);
                parse_str($qs, $params);

                $query = array_merge($params, $query);
            }
        }

        $queryString = isset($components['query']) ? html_entity_decode($components['query']) : '';
        parse_str($queryString, $qs);
        if (is_array($qs)) {
            $query = array_replace($qs, $query);
            $queryString = http_build_query($query);
        }

        $uri = $components['path'];
        if (!empty($query)) {
            $uri .= '?'.$queryString;
        }

        $server = array_replace($defaults, $server, array(
            'REQUEST_METHOD'       => strtoupper($method),
            'PATH_INFO'            => '',
            'REQUEST_URI'          => $uri,
            'QUERY_STRING'         => $queryString,
            'REDIRECT_URL'         => $components['path'],
        ));

        // create the request
        return new static($query, $request, $server, $cookies, $files);
    }

    //==============================
    // getHost
    // Gets the host part of the URL
    //==============================
    public function getHost()
    {
        if ($this->trustProxy && $this->headers->has('X-Forwarded-Host')) {
            // get the host
            $host = $this->headers->get('X-Forwarded-Host');
        } else {
            $host = $this->headers->get('Host');
            if (empty($host)) {
                $host = $this->server->get('SERVER_NAME');
                if (empty($host)) {
                    $host = $this->server->get('SERVER_ADDR');
                }
            }
        }

        // strip out the port number, and trim any white space
        $host = preg_replace('/:\d+$/u', '', $host);
        $host = preg_replace( "/(^\s+)|(\s+$)/us", "", $host);

        return $host;
    }

    //==============================
    // getHttpHost
    // Gets the host name, with the port attached if it is needed
    // (ie, port is left off if it is port 80 on http)
    // eg, you might get back http://localhost:4000
    //==============================
    public function getHttpHost()
    {
        $protocol = $this->getProtocol();
        $port = $this->getPort();

        // get the base part of the host (without the port)
        $host = $protocol.'://'.$this->getHost();

        // standard http is the same as getHost()
        if (($port == 80) && ($protocol=='http')) {
            return $host;
        }

        // standard https is the same as getHost()
        if (($port == 443) && ($protocol=='https')) {

            return $host;
        }

        // none standard port needs to be attached
        return $host.':'.$port;
    }

    //==============================
    // isSecure
    // Returns true if we are on a secure connection
    //==============================
    public function isSecure()
    {
        // see if we can find the https marker
        $secure = mb_strtolower($this->server->get('HTTPS'));
        if ($secure == 'on' || $secure == 1) {
            return true;
        }

        // Various proxy headers...
        if ($this->trustProxy) {
            if (strtolower($this->headers->get('X-Front-End-Https')) == 'on')
                return true;

            $secure = strtolower($this->headers->get('SSL_HTTPS'));
            if ($secure == 'on' || $secure == 1) {
                return true;
            }

            if (strtolower($this->headers->get('X-Forwarded-Proto')) == 'https') {
                return true;
            }
        }

        // Not SSL
        return false;
    }


    //==============================
    // getPort
    // Get the port number the request is on
    //==============================
    public function getPort()
    {
        if ($this->trustProxy) {
            if (strtolower($this->headers->get('X-Front-End-Https')) == 'on')
                return 443;
        }

        return (int) $this->server->get('SERVER_PORT');
    }



    //==============================
    // getProtocol
    // Get the protocol of the request (https or http)
    //==============================
    public function getProtocol()
    {
        return ($this->isSecure()) ? 'https' : 'http';
    }



    //==============================
    // getClientIp
    // Gets the clients IP address
    //==============================
    public function getClientIp()
    {
        if ($this->headers->has('Client-Ip')) {
            return $this->headers->get('Client-Ip');
        } elseif ($this->trustProxy && $this->headers->has('X-Forwarded-For')) {
            return $this->headers->get('X-Forwarded-For');
        }

        return $this->server->get('REMOTE_ADDR');
    }


    //==============================
    // isAjax
    // return true if this is an ajax request
    //==============================
    public function isAjax()
    {
        $ajax = mb_strtolower($this->headers->get('X-Requested-With'));

        return ($ajax == 'xmlhttprequest');
    }




    //==============================
    // getPath
    // Gets the path of the url.
    // eg, if the browser views http://example.com/cms/edit/34?x=3
    // then this function will return /cms/edit/34
    //==============================
    public function getPath()
    {
        // get the path directly if possible
        if ($this->server->has('REDIRECT_URL')) {
            return $this->server->get('REDIRECT_URL');
        }

        // Chop off the get params if there are any
        $path = $this->getUri();
        $pos = strpos($path, '?');
        if ($pos !== false) {
            $path = substr($path, 0, $pos);
        }

        return $path;
    }



    /**
     * Gets the URI of the current request
     * eg, if the browser views http://example.com/cms/edit/34?x=3
     * then this function will return /cms/edit/34?x=3
     * @return string
     */
    public function getUri()
    {
        return $this->server->get('REQUEST_URI');
    }





    //==============================
    // getMethod
    // Gets request method (GET, POST etc)
    //==============================
    public function getMethod()
    {
        return mb_strtoupper($this->server->get('REQUEST_METHOD'));
    }

    //==============================
    // hasSession
    // true if there is a session here
    //==============================
    public function hasSession()
    {
        return ($this->session != null);
    }

    //==============================
    // getSession
    // gets the session object if there is one
    //==============================
    /**
     * @return SessionStorage
     */
    public function getSession()
    {
        return $this->session;
    }

    //==============================
    // setSession
    // Sets the session on the request
    //==============================
    public function setSession(SessionStorageInterface $session)
    {
        $this->session = $session;
    }
}
