<?php
/**
 * LanguageFilterWidget.php
 *
 * @author  Elvyrra SAS
 * @license http://rem.mit-license.org/ MIT
 */
namespace Hawk\Plugins\Admin;

/**
 * Widget to filter language keys
 *
 * @package Plugins\Admin
 */
class LanguageFilterWidget extends Widget{
    /**
     * Constructor
     *
     * @param array $filters The filters
     */
    public function __construct($filters){
        $options = array();
        // active languages
        $languages = array();
        $allLanguages = Language::getAll('tag');
        foreach($allLanguages as $tag => $language){
            $options[$tag] = $language->label;
            if($language->active) {
                $languages[$tag] = $language;
            }
        }

        if(! in_array($filters['tag'], array_keys($allLanguages))) {
            $filters['tag'] = Lang::DEFAULT_LANGUAGE;
        }

        $param = array(
            'id' => 'language-filter-form',
            'method' => 'get',
            'action' => App::router()->getUri('language-keys-list'),
            'fieldsets' => array(
                'filters' => array(
                    'nofieldset' => true,

                    new SelectInput(array(
                        'name' => 'tag',
                        'options'  => $options,
                        'default' => $filters['tag'],
                        'style' => 'width: 80%; margin-right: 5px;',
                        'label' => Lang::get('language.filter-language-label'),
                        'after' => Icon::make(array(
                            'icon' => 'pencil',
                            'class' => 'text-primary edit-lang',
                            'title' => Lang::get('language.filter-language-edit')
                        )) .
                        (count($allLanguages) > 1 && Option::get('main.language') != $filters['tag'] && $filters['tag'] != Lang::DEFAULT_LANGUAGE ?
                            Icon::make(array(
                                'icon' => 'close',
                                'class' => 'text-danger delete-lang',
                                'title' => Lang::get('language.filter-language-delete')
                            )) :
                            ''
                        )
                    )),
                    new RadioInput(array(
                        'name' => 'keys',
                        'options' => array('missing' => Lang::get('language.filter-keys-missing'), 'all' => Lang::get('language.filter-keys-all')),
                        'default' => isset($filters['keys']) ? $filters['keys'] : 'all',
                        'label' => Lang::get('language.filter-keys-label'),
                        'labelWidth' => '100%',
                        'layout' => 'vertical',
                    ))
                    ),
                )
            );

            $this->form = new Form($param);
    }

    /**
     * Display the widget
     *
     * @return string The generated HTML
     */
    public function display(){
        return View::make(Theme::getSelected()->getView("box.tpl"), array(
            'title' => Lang::get('language.filter-filters-legend'),
            'icon' => 'filter',
            'content' => $this->form
        ));
    }
}