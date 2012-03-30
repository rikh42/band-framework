<?php
/**
 * This file is part of the Small Neat Box Framework
 * Copyright (c) 2011-2012 Small Neat Box Ltd.
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */


namespace snb\view;
use snb\core\ContainerInterface;
use snb\core\ContainerAware;


class TwigFileLoader extends ContainerAware implements \Twig_LoaderInterface
{
	protected $nameCache;

	public function __construct(ContainerInterface $container)
	{
		$this->setContainer($container);
		$this->nameCache = array();
	}


	protected function getFullPath($name)
	{
		if (!isset($this->nameCache[$name]))
		{
			$kernel = $this->container->get('kernel');
			$this->nameCache[$name] = $kernel->findResource($name, 'views');
		}
		return $this->nameCache[$name];
	}

	/**
	 * Gets the source code of the template, from its name
	 * @param $name
	 * @return string The template source code
	 */
	public function getSource($name)
	{
		return file_get_contents($this->getFullPath($name));
	}


	public function getCacheKey($name)
	{
		return $name;
	}

	public function isFresh($name, $time)
	{
		$fileTime = filemtime($this->getFullPath($name));
		return ($time > $fileTime);
	}
}
