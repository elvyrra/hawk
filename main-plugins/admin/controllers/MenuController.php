<?php
/**
 * MenuController.php
 *
 * @author  Elvyrra SAS
 * @license http://rem.mit-license.org/ MIT
 */
namespace Hawk\Plugins\Admin;

/**
 * Menu controller
 *
 * @package Plugins\Admin
 */
class MenuController extends Controller{
    /**
     * Customize the menu
     */
    public function index(){
        $items = MenuItem::getAll();

        $form = new Form(array(
            'id' => 'set-menus-form',
            'action' => App::router()->getUri('set-menu'),
            'inputs' => array(
                new HiddenInput(array(
                    'name' => 'data',
                    'default' => json_encode($items, JSON_NUMERIC_CHECK),
                    'attributes' => array(
                        'e-value' => 'JSON.stringify(items.valueOf())'
                    ),
                )),
                new SubmitInput(array(
                    'name' => 'valid',
                    'value' => Lang::get('main.valid-button'),
                )),
            ),
            'onsuccess' => 'app.refreshMenu()'
        ));

        if(!$form->submitted()) {

            $this->addKeysToJavaScript($this->_plugin . '.plugins-advert-menu-changed');

            return View::make(Plugin::current()->getView('sort-main-menu.tpl'), array(
                'form' => $form,
            ));
        }
        else{
            try {
                $items = MenuItem::getAll('id');

                $data = json_decode($form->getData('data'), true);

                foreach($data as $line){
                    $item = $items[$line['id']];
                    $item->set(array(
                        'active' => $line['active'],
                        'parentId' => $line['parentId'],
                        'order' => $line['order']
                    ));
                    $item->save();
                }

                return $form->response(Form::STATUS_SUCCESS, Lang::get($this->_plugin . '.sort-menu-success'));
            }
            catch (Exception $e) {
                return $form->response(Form::STATUS_ERROR, DEBUG_MODE ? $e->getMessage() : Lang::get($this->_plugin . '.sort-menu-error'));
            }

        }
    }
}