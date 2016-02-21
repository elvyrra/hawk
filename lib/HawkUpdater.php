<?php

/**
 * HawkUpdater.php
 *
 * @author  Elvyrra SAS
 * @license http://rem.mit-license.org/ MIT
 */

namespace Hawk;

/**
 * This class contains, for each version, a method that applies the non-code modifications (database changes for example)
 *
 * @package Core
 */
class HawkUpdater{

    /**
     * V0.7.0 : Add the table UserOption
     */
    public function v0_7_0(){
        App::db()->query(
            'CREATE TABLE IF NOT EXISTS `' . DB::getFullTablename('UserOption') . '`(
            `userId`  INT(11) NOT NULL DEFAULT 0,
            `userIp` VARCHAR(15) NOT NULL DEFAULT "",
            `plugin` VARCHAR(32) NOT NULL,
            `key` VARCHAR(64) NOT NULL,
            `value` VARCHAR(4096),
            UNIQUE INDEX(`userId`, `plugin`, `key`),
            UNIQUE INDEX(`userIp`, `plugin`, `key`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;'
        );
    }
}