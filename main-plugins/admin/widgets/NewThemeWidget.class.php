<?php
namespace Hawk\Plugins\Admin;

class NewThemeWidget extends Widget{
    public function __construct(){
        $this->form = ThemeController::getInstance()->addThemeForm();
    }

    public function display(){
        return View::make(Theme::getSelected()->getView("box.tpl"), array(
            'title' => Lang::get('admin.theme-add-title'),
            'icon' => 'plus',
            'content' => $this->form
        ));
    }
}