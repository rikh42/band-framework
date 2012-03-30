<?php
/**
 * This file is part of the Small Neat Box Framework
 * Copyright (c) 2011-2012 Small Neat Box Ltd.
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */


namespace snb\http;
use snb\http\RequestParams;


//==============================
// UploadedFile
// Represents a single uploaded file, fresh from the $_FILES array
//==============================
class UploadedFile
{
	protected $name;
	protected $size;
	protected $mimeType;
	protected $path;
	protected $error;


	//==============================
	// __construct
	// Sets up and validates the uploaded file
	//==============================
	public function __construct($path, $name, $size, $mimeType, $error)
	{
		// remember some values
		$this->size = (int)$size;
		$this->mimeType = $mimeType;
		$this->error = $error;
		$this->path = $path;

		// If we can't see the temp file, then note it as an error
		if (!is_file($this->path))
			$this->error = UPLOAD_ERR_NO_FILE;

		// we will also modify the original name to remove things we don't like
		$name = preg_replace('/[^[:alnum:]._()-]/iu', '', $name); // only some characters allowed
		$name = preg_replace('/\.{2,}/iu', '.', $name); // multiple dots are not allowed

		// prevent very long filenames - they are just annoying
		if (mb_strlen($name) > 100)
			$name = mb_substr($name, -100);

		// don't allow a missing filename
		if (mb_strlen($name) == 0)
			$name = "none";

		// finally, store this.
		$this->name = $name;
	}


	//==============================
	// isValid
	// returns true if the uploaded file appears to be valid
	// or false if there was a problem (file missing, bad, etc)
	//==============================
	public function isValid()
	{
		return $this->error === UPLOAD_ERR_OK;
	}


	//==============================
	// getOriginalName
	// Gets the name of the file as supplied by the client.
	// we limit the name to a subset of characters, so the version here
	// may be slightly different to the one supplied by the client, but it is safer
	//==============================
	public function getOriginalName()
	{
		return $this->name;
	}


	//==============================
	// getFileSize
	// The size of the uploaded file
	//==============================
	public function getFileSize()
	{
		return $this->size;
	}


	//==============================
	// getMimeType
	// returns the mime type of the file.
	// This is supplied by the client, so can not be trusted.
	// Use for information only - not action.
	//==============================
	public function getMimeType()
	{
		return $this->mimeType;
	}


	//==============================
	// getTempFilePath
	// Gets the path to the temp file where it is placed
	//==============================
	public function getTempFilePath()
	{
		if (($this->isValid()) && (is_uploaded_file($this->path)))
			return $this->path;

		return null;
	}


	//==============================
	// move
	// Moves to the file to a new location
	//==============================
	public function move($target)
	{
		// attempt to validate and move the file
		if (($this->isValid()) && (is_uploaded_file($this->path)))
			return move_uploaded_file($this->path, $target);

		// failed to move the file (not valid or not an uploaded file)
		return false;
	}
}

