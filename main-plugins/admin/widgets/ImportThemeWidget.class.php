<?php

namespace Hawk\Plugins\Admin;

class ImportThemeWidget extends Widget{
    public function __construct(){
        $this->form = ThemeController::getInstance()->importThemeForm();
    }

    public function display(){
        return View::make(Theme::getSelected()->getView("box.tpl"), array(
            'title' => Lang::get('admin.theme-import-title'),
            'icon' => 'upload',
            'content' => $this->form
        ));
    }
}