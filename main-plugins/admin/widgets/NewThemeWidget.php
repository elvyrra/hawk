<?php
/**
 * NewThemeWidget.php
 *
 * @author  Elvyrra SAS
 * @license http://rem.mit-license.org/ MIT
 */
namespace Hawk\Plugins\Admin;

/**
 * NewThemeWidget
 *
 * @package Plugins\Admin
 */
class NewThemeWidget extends Widget{
    /**
     * Constructor
     */
    public function __construct(){
        $this->form = ThemeController::getInstance()->addThemeForm();
    }

    /**
     * Display the widget
     *
     * @return string The generated HTML
     */
    public function display(){
        return View::make(Theme::getSelected()->getView("box.tpl"), array(
            'title' => Lang::get('admin.theme-add-title'),
            'icon' => 'plus',
            'content' => $this->form
        ));
    }
}
