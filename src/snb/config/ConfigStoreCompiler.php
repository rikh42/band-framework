<?php
/**
 * This file is part of the Small Neat Box Framework
 * Copyright (c) 2011-2012 Small Neat Box Ltd.
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

namespace snb\config;

use snb\core\KernelInterface;


/**
 * Converts a set of config settings in a PHP class that can be loaded more efficiently.
 */
class ConfigStoreCompiler
{
    protected $settings;
    protected $environment;
    protected $path;
    protected $source;
    protected $hash;


    public function __construct(KernelInterface $kernel)
    {
        $this->settings = array();
        $this->path = $kernel->getPackagePath('app');
        $this->source = '';
        $this->setEnvironment('dev');
    }


    public function setValues($settings)
    {
        $this->settings = $settings;
    }

    public function setEnvironment($env)
    {
        $this->environment = $env;
        $this->hash = md5($env);
    }

    public function setCachePath($path)
    {
        $this->path = $path;
    }


    public function compile()
    {
        foreach ($this->settings as $name => $value)
        {
            $this->repr($name);
            $this->raw(' => ');
            $this->repr($value);
            $this->raw(",\n");
        }

        $date = date('d M Y H:i:s');
        $output = <<<END
<?php
/**
 * This file is Generated from the config yml files. Do not edit
 * Generated on {$date} for environment '{$this->environment}'
 */

namespace snb\config;
use snb\config\ConfigStoreInterface;

class ConfigStore implements ConfigStoreInterface
{
	public function getSettings()
	{
		return array({$this->source});
	}
}

END;

        // Write the content out....
        file_put_contents($this->path, $output);
    }


    /**
     * The Following code taken from Twig
     * (c) 2009 Fabien Potencier
     * (c) 2009 Armin Ronacher
     *
     * For the full copyright and license information, please view the LICENSE
     * file that was distributed with this source code.
     */

    /**
     * Returns a PHP representation of a given value.
     *
     * @param mixed $value The value to convert
     *
     * @return Twig_Compiler The current compiler instance
     */
    public function repr($value)
    {
        if (is_int($value) || is_float($value)) {
            if (false !== $locale = setlocale(LC_NUMERIC, 0)) {
                setlocale(LC_NUMERIC, 'C');
            }

            $this->raw($value);

            if (false !== $locale) {
                setlocale(LC_NUMERIC, $locale);
            }
        } elseif (null === $value) {
            $this->raw('null');
        } elseif (is_bool($value)) {
            $this->raw($value ? 'true' : 'false');
        } elseif (is_array($value)) {
            $this->raw('array(');
            $i = 0;
            foreach ($value as $key => $value) {
                if ($i++) {
                    $this->raw(', ');
                }
                $this->repr($key);
                $this->raw(' => ');
                $this->repr($value);
            }
            $this->raw(')');
        } else {
            $this->string($value);
        }

        return $this;
    }

    /**
     * Adds a quoted string to the compiled code.
     *
     * @param string $value The string
     *
     * @return Twig_Compiler The current compiler instance
     */
    public function string($value)
    {
        $this->source .= sprintf('"%s"', addcslashes($value, "\0\t\"\$\\"));

        return $this;
    }

    /**
     * Adds a raw string to the compiled code.
     *
     * @param string $string The string
     *
     * @return Twig_Compiler The current compiler instance
     */
    public function raw($string)
    {
        $this->source .= $string;

        return $this;
    }

}