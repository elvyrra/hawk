<?php
/**
 * NewMediaWidget.php
 *
 * @author  Elvyrra SAS
 * @license http://rem.mit-license.org/ MIT
 */
namespace Hawk\Plugins\Admin;

/**
 * NewMediaWidget
 *
 * @package Plugins\Admin
 */
class NewMediaWidget extends Widget{
    /**
     * Constructor
     */
    public function __construct(){
        $this->form = ThemeController::getInstance()->addMediaForm();
    }

    /**
     * Display the widget
     *
     * @return string The generated HTML
     */
    public function display(){
        return View::make(Theme::getSelected()->getView("box.tpl"), array(
            'title' => Lang::get('admin.theme-add-media-title'),
            'icon' => 'plus',
            'content' => $this->form
        ));
    }
}
