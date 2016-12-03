<?php

namespace Hawk\Plugins\LangSwitcher;

Event::on(\Hawk\Plugins\Main\MainController::EVENT_AFTER_GET_MENUS, function(Event $event){
    $languages = Language::getAllActive();

    if(count($languages) > 0){
        $menus = $event->getData('menus');

        $menu = new MenuItem(array(
            'id' => uniqid(),
            'plugin' => 'lang-switcher',
            'name' => 'selector',
            'label' => strtoupper(LANGUAGE),
        ));

        foreach($languages as $language){
            $menu->visibleItems[] = new MenuItem(array(
                'id' => uniqid(),
                'plugin' => 'lang-switcher',
                'name' => $language->tag,
                'label' =>
                    Icon::make(array(
                        'size' => 'fw',
                        'icon' => $language->tag == LANGUAGE ? 'check' : ''
                    )) .
                    strtoupper($language->tag),
                'action' => 'javascript: $.cookie("language", "' . $language->tag . '", {path : "/"}); location = app.getUri("index");'
            ));
        }

        $menus['settings'][] = $menu;

        $event->setData('menus', $menus);
    }
});