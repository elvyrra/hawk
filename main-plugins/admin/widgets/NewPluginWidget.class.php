<?php
namespace Hawk\Plugins\Admin;

class NewPluginWidget extends Widget{
    private function form(){
        $form = new Form(array());

        return $form;
    }

    /**
     * Display the plugin creation form
     */
    public function display(){
        $form = $this->form();

        return $form->display();
    }   

    /**
     * Compute the plugin creation
     */
    public function create(){
        $form = $this->form();
    }
}