<?php
/**
 * This file is part of the Small Neat Box Framework
 * Copyright (c) 2011-2012 Small Neat Box Ltd.
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */


namespace snb\view;
use snb\view\ViewInterface;
use snb\core\ContainerAware;
use snb\view\RouteExtension;


/**
 * View class that lets us use Twig for templates
 */
class TwigView extends ContainerAware implements ViewInterface
{
	/**
	 * Needed for Container Aware
	 */
	public function __construct()
	{
	}


	/**
	 * Renders a template
	 * @param string $name - name of the template file
	 * @param array $data - data to be passed to the template engine
	 * @return string
	 */
	public function render($name, array $data=array())
	{
		/**
		 * Get the config
		 * @var snb\core\ConfigInterface $config
		 */
		$config = $this->container->get('config');
		$kernel = $this->container->get('kernel');

		// Find the cache path
		$cachePath = $config->get('twig.cache', ':/cache');

		// Use our loader the know how to map resource names to filenames
		$loader = new TwigFileLoader($this->container);

		// use the default environment. Should pass settings over from the config
		$twig = new \Twig_Environment($loader,array(
			'cache' => $kernel->findPath($cachePath),
			'auto_reload' => true
		));

		// Add all the extensions that have been registed with the service provider
		$extensions = $this->container->getMatching('twig.extension.*');
		foreach ($extensions as $ext)
			$twig->addExtension($ext);

		// Load the template and render it.
		$template = $twig->loadTemplate($name);
		return $template->render($data);
	}
}

