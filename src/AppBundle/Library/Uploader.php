<?php
// src/AppBundle/Library/Uploader.php

namespace AppBundle\Library;

use Symfony\Component\HttpFoundation\File\UploadedFile;

class Uploader
{
	protected $targetDir;
	
	public function getTargetDir()
	{
		return $this->targetDir;
	}
	
	public function __construct($targetDir)
	{
		$this->targetDir = $targetDir;
	}
	
	/**
	 *	Checks file
	 *	return boolean
	 */
	public function check()
	{
		return true;
	}
	
	/**
	 *	Moves uploaded file to target directory
	 *	return boolean
	 */
	public function saveUploaded(UploadedFile $file, $fileName = null)
	{
		if ($fileName == null) {
			$fileName = md5(uniqid());
		}
		$file->move($this->targetDir, $fileName);
		
		return $this->targetDir . "/" . $fileName;
	}
	
	/**
	 *	Uploads file from @url
	 *	return boolean
	 */
	public function uploadFromUrl($url, $fileName = null)
	{
		if (!$url) {
			return false;
		}
		
		$fileContent = '';
		try {
			if (false == ($fileContent = file_get_contents($url))) {
				return false;
			}
		} catch (\Exception $e) {
			return false;
		}
		
		if ($fileName == null) {
			$fileName = md5(uniqid());
		}
		$fileName = $this->targetDir . "/" . $fileName;
		file_put_contents($fileName, $fileContent);
		return $fileName;
	}
}