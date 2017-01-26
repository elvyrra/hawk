<?php
/**
 * ImportThemeWidget.php
 *
 * @author  Elvyrra SAS
 * @license http://rem.mit-license.org/ MIT
 */
namespace Hawk\Plugins\Admin;

/**
 * Widget to import theme
 *
 * @package Plugins\Admin
 */
class ImportThemeWidget extends Widget{

    /**
     * Constructor
     */
    public function __construct(){
        $this->form = ThemeController::getInstance()->importThemeForm();
    }

    /**
     * Display the widget
     *
     * @return string The generated HTML
     */
    public function display(){
        return View::make(Theme::getSelected()->getView("box.tpl"), array(
            'title' => Lang::get('admin.theme-import-title'),
            'icon' => 'upload',
            'content' => $this->form
        ));
    }
}