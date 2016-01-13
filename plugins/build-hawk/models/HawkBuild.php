<?php

namespace Hawk\Plugins\BuildHawk;

class HawkBuild extends Model{
	protected static $tablename = 'HawkBuild';

	const VERSION_PATTERN = '/(\d+\.){2,3}\d+$/';

    const REPOSITORY_DIR = '/home/devs/hawk/';

	const STATUS_OPEN = 0;
	const STATUS_BUILT = 10;
	const STATUS_TESTED = 20;
	const STATUS_DEPLOYED = 30;

	public static $status = array(
		self::STATUS_OPEN => 'open',
		self::STATUS_BUILT => 'built',
		self::STATUS_TESTED => 'tested',
		self::STATUS_DEPLOYED => 'deployed',
	);

	/**
	 * Get an instance from it version
	 */
	public static function getByVersion($version){
		return HawkBuild::getByExample(new DBExample(
			array('version' => $version)
		));
	}

	/**
	 * Get the version tag 
	 */
	public function getTag(){
		return 'v' . $this->version;
	}


	/**
	 * Get the build folder
	 */
	public function getBuildDirname(){
		return Plugin::current()->getUserfilesDir() . $this->getTag() . '/';
	}

	/**
	 * Get the built zip file
	 */
	public function getBuildZipFilename(){
		return Plugin::current()->getUserfilesDir() . 'builds/' . 'update-' . $this->getTag() . '.zip';
	}


}