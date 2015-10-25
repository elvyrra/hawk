<?php
/**
 * Updater.class.php
 */

namespace {{ $namespace }};

/**
 * This class is used when a user updates your plugin to a newer version. 
 * For each version you deploy, if you need to apply non code modification (like table structure changes),
 * create a method vx_y_z (corresponding to version vx.y.z) that contains the action to perform on update.
 * If no such modifications are pushed on a version, you don't need to created the associated method
 */
class Updater extends PluginInstaller{
    const PLUGIN_NAME = '{{ $name }}';
    
    /**
     * Create here your update methods like the following example :
     */
    /*
    public function v0_0_1(){
        DB::get(MAINDB)->query(' ... ');
    }
    */
}