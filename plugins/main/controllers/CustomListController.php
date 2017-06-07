<?php

namespace Hawk\Plugins\Main;

/**
 * This controller is used to customize a list that is customizable
 */
class CustomListController extends Controller {
    public function customize() {
        $allFields = App::request()->getParams('fields');
        $displayedFields = App::request()->getParams('displayed');

        $form = new Form(array(
            'id' => 'customize-list',
            'fieldsets' => array(
                'form' => array(
                    new HiddenInput(array(
                        'name' => 'displayedFields',
                        'default' => $displayedFields,
                        'attributes' => array(
                            'e-value' => 'displayedFields.toString()'
                        )
                    )),

                    new HiddenInput(array(
                        'name' => 'allFields',
                        'default' => $allFields
                    ))
                ),
                'submits' => array(
                    new SubmitInput(array(
                        'name' => 'valid',
                        'value' => Lang::get('main.valid-button')
                    )),

                    new ButtonInput(array(
                        'name' => 'cancel',
                        'value' => Lang::get('main.cancel-button'),
                        'onclick' => 'app.dialog("close")'
                    ))
                )
            ),
            'onsuccess' => 'app.dialog("close");app.lists["' . $this->id . '"].rebuild();'
        ));

        if(!$form->submitted()) {
            $this->addJavaScript($this->getPlugin()->getJsUrl('customize-list.js'));
            $this->addCss($this->getPlugin()->getCssUrl('customize-list.less'));

            $content = View::make($this->getPlugin()->getView('customize-list.tpl'), array(
                'form' => $form
            ));

            return Dialogbox::make(array(
                'title' => Lang::get($this->_plugin . '.customize-list-title'),
                'icon' => 'filter',
                'page' => $content
            ));
        }
        else {
            $listOptions = json_decode(App::session()->getUser()->getOptions($this->_plugin . '.list-' . $this->id));

            if(!$listOptions) {
                $listOptions = (object) array();
            }

            $listOptions->displayedFields = json_decode(App::request()->getBody('displayedFields'));

            // Remove sorts and filters that are not anymore displayed
            $fields = json_decode($allFields);

            foreach($fields as $field) {
                $name = $field->name;
                if(!in_array($name, $listOptions->displayedFields)) {
                    if(isset($listOptions->sorts->$name)) {
                        unset($listOptions->sorts->$name);
                    }

                    if(isset($listOptions->searches->$name)) {
                        unset($listOptions->searches->$name);
                    }
                }
            }

            App::session()->getUser()->setOption($this->_plugin . '.list-' . $this->id, json_encode($listOptions));

            return $form->response(Form::STATUS_SUCCESS);
        }
    }
}