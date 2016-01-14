<?php
/**
 * Installer.class.php
 */

namespace Hawk\Plugins\StaffManager;

/**
 * This class describes the behavio of the installer for the plugin {$data['name']}
 */
class Installer extends PluginInstaller{
    const PLUGIN_NAME = 'staffManager';
    
    /**
     * Install the plugin. This method is called on plugin installation, after the plugin has been inserted in the database
     */
    public function install(){
        // Add Table 'TicketProject' in database if not exist
        DB::get(MAINDB)->query(
            'CREATE TABLE IF NOT EXISTS ' . StaffAbsence::getTable() . ' (
                `id` INT(11) NOT NULL PRIMARY KEY AUTO_INCREMENT,
                `description` TEXT NOT NULL,
                `userId` int NOT NULL,
                `status` VARCHAR(32) NOT NULL,
                `author` int NOT NULL,
                `startDate` DATE,
                `endDate` DATE,
                `mtime` INT(11)
            )ENGINE=InnoDB DEFAULT CHARSET=utf8;'
        );
    }

    /**
     * Uninstall the plugin. This method is called on plugin uninstallation, after it has been removed from the database
     */
    public function uninstall(){
        DB::get(MAINDB)->query('DROP TABLE IF EXISTS ' . StaffAbsence::getTable());
    }

    /**
     * Activate the plugin. This method is called when the plugin is activated, just after the activation in the database
     */
    public function activate(){
        $permission = Permission::add(self::PLUGIN_NAME . '.staff-management', 0, 0);

        $menu = MenuItem::add(array(
            'plugin' => self::PLUGIN_NAME,
            'name' => 'absence-manager',
            'labelkey' => self::PLUGIN_NAME . '.menu-absence-manager-title',
        )); 

        MenuItem::add(array(
            'plugin' => self::PLUGIN_NAME,
            'name' => 'my-absence',
            'labelkey' => self::PLUGIN_NAME . '.menu-my-absence-title',
            'action' => 'staffManager-my-absence',
            'parentId' => $menu->id,
        ));   

        MenuItem::add(array(
            'plugin' => self::PLUGIN_NAME,
            'name' => 'calendar-absence',
            'labelkey' => self::PLUGIN_NAME . '.menu-calendar-absence-title',
            'action' => 'staffManager-calendar-absence',
            'parentId' => $menu->id,
        ));

        MenuItem::add(array(
            'plugin' => self::PLUGIN_NAME,
            'name' => 'new-absence',
            'labelkey' => self::PLUGIN_NAME . '.menu-new-absence-title',
            'action' => 'staffManager-new-absence',
            'parentId' => $menu->id,
            'target' => 'dialog'
        ));


        $menuTeam = MenuItem::add(array(
            'plugin' => self::PLUGIN_NAME,
            'name' => 'team-manager',
            'labelkey' => self::PLUGIN_NAME . '.menu-team-manager-title',
            'permissionId' => $permission->id
        )); 

        MenuItem::add(array(
            'plugin' => self::PLUGIN_NAME,
            'name' => 'team-absence-manager',
            'labelkey' => self::PLUGIN_NAME . '.menu-team-absence-manager-title',
            'action' => 'staffManager-team-absence-manager',
            'parentId' => $menuTeam->id,
            'permissionId' => $permission->id
        ));
        
    }
    
    /**
     * Deactivate the plugin. This method is called when the plugin is deactivated, just after the deactivation in the database
     */
    public function deactivate(){
        MenuItem::getByName(self::PLUGIN_NAME . '.absence-manager')->delete();

        MenuItem::getByName(self::PLUGIN_NAME . '.my-absence')->delete();

        MenuItem::getByName(self::PLUGIN_NAME . '.calendar-absence')->delete();

        MenuItem::getByName(self::PLUGIN_NAME . '.new-absence')->delete();

        MenuItem::getByName(self::PLUGIN_NAME . '.team-manager')->delete();

        MenuItem::getByName(self::PLUGIN_NAME . '.team-absence-manager')->delete();

        Permission::getByName(self::PLUGIN_NAME . '.staff-management')->delete();
    }

    /**
     * Configure the plugin. This method contains a page that display the plugin configuration. To treat the submission of the configuration
     * you'll have to create another method, and make a route which action is this method. Uncomment the following function only if your plugin if 
     * configurable.
     */
    public function settings(){
        $param = array(
            'id' => 'staffManager-settings-form', 
            'fieldsets' => array(
                'general' => array(
                    new TextareaInput(array(
                        'name' => 'typeAbsence',
                        'required' => true,
                        'label' => Lang::get('staffManager.typeAbsence-form-options-label'),
                        'labelClass' => 'required',
                        'attributes' => array(
                            'data-bind' => "value : typeAbsence",
                        ),
                        'default' => Option::get('staffManager.typeAbsence') ? implode(PHP_EOL, array_keys(json_decode(Option::get('staffManager.typeAbsence'), true))) : "",
                        'cols' => 20,
                        'rows' => 10
                    )),

                    new TextareaInput(array(
                        'name' => 'status',
                        'required' => true,
                        'label' => Lang::get('staffManager.status-form-options-label'),
                        'labelClass' => 'required',
                        'attributes' => array(
                            'data-bind' => "value : status",
                        ),
                        'default' => Option::get('staffManager.status') ? implode(PHP_EOL, array_keys(json_decode(Option::get('staffManager.status'), true))) : "",
                        'cols' => 20,
                        'rows' => 10
                    )),

                    new TextInput(array(
                        'name' => 'statusAsk',
                        'required' => true,
                        'label' => Lang::get('staffManager.statusAsk-form-options-label'),
                        'labelClass' => 'required',
                        'attributes' => array(
                            'data-bind' => "value : statusAsk",
                        ),
                        'default' => Option::get('staffManager.statusAsk'),
                    ))
                ),          
                
                '_submits' => array(
                    new SubmitInput(array(
                        'name' => 'valid',
                        'value' => Lang::get('staffManager.valid-button')
                    )),

                    new ButtonInput(array(
                        'name' => 'cancel',
                        'value' => Lang::get('staffManager.cancel-button'),
                        'onclick' => 'app.dialog("close")'
                    ))
                ),
            ),
            'onsuccess' => 'app.dialog("close");'
        );

        $form = new Form($param);
        
        if(!$form->submitted()){
            return View::make(Theme::getSelected()->getView("dialogbox.tpl"), array(
                'page' => $form,
                'title' => Lang::get('staffManager.settings-form-title'),
                'icon' => 'cogs',
            ));
        }
        else{
            if($form->check()){     
                Option::set('staffManager.statusAsk',$form->getData("statusAsk"));

                $keys = array();
                foreach(explode(PHP_EOL, $form->getData("status")) as $i => $option){
                    if(!empty($option)){
                        $keys[trim($option)] = trim($option);
                    }
                }   
                
                if(! array_key_exists($form->getData("statusAsk") , $keys))
                    $keys[$form->getData("statusAsk")] = trim($form->getData("statusAsk"));

                Option::set('staffManager.status', json_encode($keys));

                $keys = array();
                foreach(explode(PHP_EOL, $form->getData("typeAbsence")) as $i => $option){
                    if(!empty($option)){
                        $keys[trim($option)] = trim($option);
                    }
                }   

                Option::set('staffManager.typeAbsence', json_encode($keys));




                return $form->response(Form::STATUS_SUCCESS);       
            }
        }
    }
    
}