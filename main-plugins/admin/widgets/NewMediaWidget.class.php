<?php
namespace Hawk\Plugins\Admin;

class NewMediaWidget extends Widget{
    public function __construct(){
        $this->form = ThemeController::getInstance()->addMediaForm();
    }


    public function display(){
        return View::make(ThemeManager::getSelected()->getView("box.tpl"), array(
            'title' => Lang::get('admin.theme-add-media-title'),
            'icon' => 'plus',
            'content' => $this->form
        ));
    }
}