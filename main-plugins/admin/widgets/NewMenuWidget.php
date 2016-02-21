<?php
/**
 * NewMenuWidget.php
 *
 * @author  Elvyrra SAS
 * @license http://rem.mit-license.org/ MIT
 */
namespace Hawk\Plugins\Admin;

/**
 * NewMenuWidget
 *
 * @package Plugins\Admin
 */
class NewMenuWidget extends Widget{
    /**
     * Generate the widget form
     *
     * @param int $itemId The menu item id
     *
     * @return Form         The generated form
     */
    public function form($itemId){
        $name = uniqid();

        return new Form(array(
            'id' => 'new-menu-form',
            'model' => 'MenuItem',
            'action' => App::router()->getUri('edit-item', array('itemId' => $itemId)),
            'reference' => array('id' => $itemId),
            'fieldsets' => array(
                'parameters' => array(
                    new HiddenInput(array(
                        'name' => 'plugin',
                        'value' => 'custom'
                    )),
                    new HiddenInput(array(
                        'name' => 'parentId',
                        'value' => 0
                    )),
                    new HiddenInput(array(
                        'name' => 'active',
                        'value' => 0
                    )),
                    new HiddenInput(array(
                        'name' => 'name',
                        'value' => $name
                    )),
                    new HiddenInput(array(
                        'name' => 'labelKey',
                        'value' => 'custom.menu-item-' . $name . '-title'
                    ))
                ),
                'labels' => array_map(function ($language) {
                    return new TextInput(array(
                        'name' => 'label[' . $language->tag . ']',
                        'independant' => true,
                        'label' => Lang::get('admin.new-menu-form-label', array('language' => $language->tag))
                    ));
                }, Language::getAllActive()),
                'submits' => array(
                    new SubmitInput(array(
                        'name' => 'valid',
                        'value' => Lang::get('main.valid-button')
                    ))
                )
            ),
        ));
    }

    /**
     * Display the widget
     *
     * @return string The generated HTML
     */
    public function display(){
        $form = MenuController::getInstance()->customMenuItemForm(0);
        return $form->display();
    }
}