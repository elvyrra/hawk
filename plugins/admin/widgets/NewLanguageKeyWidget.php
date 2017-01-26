<?php
/**
 * NewLanguageKeyWidget.php
 *
 * @author  Elvyrra SAS
 * @license http://rem.mit-license.org/ MIT
 */
namespace Hawk\Plugins\Admin;

/**
 * NewLanguageKeyWidget
 *
 * @package Plugins\Admin
 */
class NewLanguageKeyWidget extends Widget{
    /**
     * Display the widget
     *
     * @return string The generated HTML
     */
    public function display(){
        $form = LanguageController::getInstance()->keyForm();

        return View::make(Theme::getSelected()->getView("box.tpl"), array(
            'title' => Lang::get('language.key-form-add-title'),
            'icon' => 'font',
            'content' => $form
        ));
    }
}
