<?php

abstract class PluginInstaller {
    private $plugin; 

    public function __construct($plugin){
        $this->plugin = $plugin;
    }

    abstract public function install();

    abstract public function uninstall();

    abstract public function activate();

    abstract public function deactivate();
}