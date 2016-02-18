<?php
namespace Hawk\Plugins\Admin;

class SearchPluginWidget extends Widget{
    public function display(){

        $form = new Form(array(
            'id' => 'search-plugins-form',
            'method' => 'get',
            'action' => App::router()->getUri('search-plugins'),
            'inputs' => array(
                new TextInput(array(
                    'name' => 'search',
                    'required' => true,
                    'default' => App::request()->getParams('search'),
                    'placeholder' => Lang::get('admin.search-plugin-form-search-label'),
                )),

                new SubmitInput(array(
                    'name' => 'valid',
                    'value' => Lang::get('admin.search-plugin-form-submit-btn'),
                    'icon' => 'search'
                )),
            )
        ));

        return View::make(Theme::getSelected()->getView('box.tpl'), array(
            'content' => $form,
            'title' => Lang::get('admin.search-plugin-form-title'),
            'icon' => 'search'
        ));
    }
}