<?php
/**
 * This file is part of the Small Neat Box Framework
 * Copyright (c) 2011-2012 Small Neat Box Ltd.
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

namespace snb\core;

//==============================
// Translate
// Support for language translation
// Translations are loaded from csv files. The csv file is expected to have 2 columns
// The first is the name of the text item, and the second is the text for that item
// in the appropriate language.
// You are expected to use a directory structure style system for the name of items
// eg cms/signin/welcome to avoid name clashes.
// So the above example states that the text item is part of the cms, and part of the sign in
// screen, and is the welcome message.
// The system looks for the files in a folder for each language. It defaults to en for english
//==============================
class Translate
{
    protected $language;
    protected $dictionary;
    protected $path;

    protected static $instance = null;

    //==============================
    // get
    // Singleton support. Gets the one and only translate object
    //==============================
    /** @return Translate */
    public static function get()
    {
        if (self::$instance == null) {
            self::$instance = new Translate();
        }

        return self::$instance;
    }

    //==============================
    // get
    // Singleton support. Gets the one and only translate object
    //==============================
    public function __construct()
    {
        $this->language = 'en';
        $this->dictionary = array();
        $this->path = null;
    }

    //==============================
    // setLanguage
    // Sets the language to use for future calls.
    // $lang should be a 2 letter language identifier
    //==============================
    public function setLanguage($lang)
    {
        if ($lang != $this->language) {
            // set the language
            $this->language = $lang;

            // reset the dictionary after the language has been changed
            $this->dictionary = array();
        }
    }

    //==============================
    // getText
    // Looks up the named item in the dictionary and return it.
    // You look up something like /cms/signin/welcome and get the local language version
    // of the text back (eg "Please enter your details to sign in")
    //==============================
    public function getText($name)
    {
        // See if the name is already in the dictionary, and if so, just return it
        if (array_key_exists($name, $this->dictionary)) {
            return $this->dictionary[$name];
        }

        // no luck, so add a blank entry and then try and load a better version
        $this->dictionary[$name] = "[missing text: $name]";
        $this->loadTranslations($name);

        // finally return the translation, if there was one
        return $this->dictionary[$name];
    }

    //==============================
    // setTranslationPath
    // Tells the translation system where to look for translation file
    // This is the root folder. For example, if you pass in /foo/lang
    // we will search for text in /foo/lang/<language code>/file.csv
    //==============================
    public function setTranslationPath($path)
    {
        $this->path = (string) $path;
    }

    //==============================
    // debug
    // Builds a list of all the entries in the system that have not been defined
    //==============================
    public function debug()
    {
        $ret = array();
        foreach ($this->dictionary as $key => $value) {
            if (preg_match('/^\[missing text:/i', $value)) {
                // This looks like a missing value
                $ret[] = $key;
            }
        }

        return $ret;
    }

    //==============================
    // loadTranslations
    // Support function that will attempt to load an appropriate translation file
    // and fill the dictionary will its contents.
    //==============================
    protected function loadTranslations($name)
    {
        if ($this->path == null) {
            return;
		}

        // no luck, so we will have to go load in a translation file.
        $path = $this->path.'/'.$this->language.'/';

        // name uses the directory naming convention
        // eg cms/signin/welcome
        // we look for the translation files like so...
        // 		en/cms-signin.csv
        // 		en/cms.csv
        // 		en/default.csv

        $parts = explode('/', mb_strtolower($name));
        while (count($parts) > 0) {
            $filename = $path . implode('-', $parts) .'.csv';
            if (file_exists($filename )) {
                $this->loadFile($filename);

                return;
            }
            array_pop($parts);
        }

        // finally try default.csv
        $filename = $path . 'default.csv';
        if (file_exists($filename )) {
            $this->loadFile($filename);
        }

        // If we only have a partial translation of the text,
        // then we will look in the english folder first, and fill in the dictionary
        // then we will read in the correct language file and replace the english for any items that exists
    }

    //==============================
    // loadFile
    // loads in the translation file and fills the dictionary with its values
    //==============================
    protected function loadFile($filename)
    {
        // reads in the csv file...
        $handle = fopen($filename, "r");
        if ($handle !== FALSE) {
            $data = fgetcsv($handle);
            while ($data !== FALSE) {
                // we have a line of data from the csv.
                // We are interested in lines with 2 entries (the name of the text, plus the actual text)
                $num = count($data);
                if ($num == 2) {
                    // this row has 2 items in it, so assume this is a dictionary entry
                    $key = (string) $data[0];
                    $value = (string) $data[1];
                    $this->dictionary[$key] = $value;
                }

                // see if the there is more...
                $data = fgetcsv($handle);
            }

            // we're done - clean up
            fclose($handle);
        }
    }
}
