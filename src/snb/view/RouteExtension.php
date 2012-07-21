<?php
/**
 * This file is part of the Small Neat Box Framework
 * Copyright (c) 2011-2012 Small Neat Box Ltd.
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

namespace snb\view;
use snb\routing\UrlGeneratorInterface;

/**
 * A Twig extension that adds functions that convert route names into urls
 */
class RouteExtension extends \Twig_Extension
{
    private $generator;

    public function __construct(UrlGeneratorInterface $gen)
    {
        $this->generator = $gen;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'routing';
    }

    /**
     * Returns a list of functions to add to the existing list.
     *
     * @return array An array of functions
     */
    public function getFunctions()
    {
        return array(
            'url'  => new \Twig_Function_Method($this, 'getUrl'),
            'path' => new \Twig_Function_Method($this, 'getPath'),
        );
    }

    /**
     * Convert the route into a path, relative to the root of the domain
     * @param $name - name of the route
     * @param  array $parameters - arguments for the route
     * @return mixed
     */
    public function getPath($name, $parameters = array())
    {
        return $this->generator->generate($name, $parameters, false);
    }

    /**
     * convert the route name to a fully qualified domain name
     * @param $name
     * @param  array $parameters
     * @return mixed
     */
    public function getUrl($name, $parameters = array())
    {
        return $this->generator->generate($name, $parameters, true);
    }
}
