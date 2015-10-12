<?php

/**
 * HawkUpdater.class.php
 * @author Elvyrra SAS
 */

namespace Hawk;

/**
 * This class contains, for each version, a method that applies the non-code modifications (database changes for example)
 */
class HawkUpdater{

	public function v0_0_3(){
		file_put_contents(ROOT_DIR . 'v0.0.3', 'test');
	}

	public function v0_0_4(){
		file_put_contents(ROOT_DIR . 'v0.0.4', 'test');
	}
}