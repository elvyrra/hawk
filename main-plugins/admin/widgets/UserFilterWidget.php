<?php
/**
 * UserFilterWidget.php
 *
 * @author  Elvyrra SAS
 * @license http://rem.mit-license.org/ MIT
 */

namespace Hawk\Plugins\Admin;

/**
 * This Widget is used to filter the users list by status or role
 *
 * @package Plugins\Admin
 */
class UserFilterWidget extends Widget{
    /**
     * Get the user filters
     *
     * @return array The user filters
     */
    public function getFilters(){
        if(App::request()->getHeaders('X-List-Filter')) {
            App::session()->getUser()->setOption('admin.user-filter', App::request()->getHeaders('X-List-Filter'));
        }

        return json_decode(App::session()->getUser()->getOptions('admin.user-filter'), true);
    }

    /**
     * Display the widget
     *
     * @return string The generated HTML
     */
    public function display(){
        $filters = $this->getFilters();

        $form = new Form(array(
            'id' => 'user-filter-form',
            'fieldsets' => array(
                'form' => array(
                    new RadioInput(array(
                        'name' => 'status',
                        'labelWidth' => '100%',
                        'label' => Lang::get('admin.user-filter-status-label'),
                        'layout' => 'vertical',
                        'value' => isset($filters['status']) ? $filters['status'] : -1,
                        'options' => array(
                            '-1' => Lang::get('admin.user-filter-status-all'),
                            '1' => Lang::get('admin.user-filter-status-active'),
                            '0' => Lang::get('admin.user-filter-status-inactive')
                        ),
                    ))
                )
            )
        ));

        return View::make(Theme::getSelected()->getView("box.tpl"), array(
            'content' => $form,
            'title' => Lang::get('admin.user-filter-legend'),
            'icon' => 'filter',
        ));
    }
}
