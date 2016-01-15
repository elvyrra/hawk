<?php
/**
 * Installer.class.php
 */

namespace Hawk\Plugins\Ticket;

/**
 * This class describes the behavio of the installer for the plugin {$data['name']}
 */
class Installer extends PluginInstaller{
    const PLUGIN_NAME = 'ticket';
    
    /**
     * Install the plugin. This method is called on plugin installation, after the plugin has been inserted in the database
     */
    public function install(){
        // Add Table 'TicketProject' in database if not exist
        DB::get(MAINDB)->query(
            'CREATE TABLE IF NOT EXISTS ' . TicketProject::getTable() . ' (
                `id` INT(11) NOT NULL PRIMARY KEY AUTO_INCREMENT,
                `name` VARCHAR(32) NOT NULL UNIQUE,
                `description` TEXT NOT NULL,
                `author` int NOT NULL,
                `status` VARCHAR(32) NOT NULL,
                `mtime` INT(11)
            )ENGINE=InnoDB DEFAULT CHARSET=utf8;'
        );

        // Add Table 'Ticket' in database if not exist
        DB::get(MAINDB)->query(
            'CREATE TABLE IF NOT EXISTS ' . Ticket::getTable() . ' (
                `id` INT(11) NOT NULL PRIMARY KEY AUTO_INCREMENT, 
                `projectId` INT(11) NOT NULL,
                `title` VARCHAR(256) NOT NULL,
                `description` TEXT NOT NULL,
                `status` VARCHAR(32) NOT NULL,
                `author` INT(11) NOT NULL,
                `target` INT(11) NOT NULL,
                `deadLine` DATE,
                `mtime` INT(11),    
                FOREIGN KEY (`projectId`) REFERENCES `' . TicketProject::getTable() .'` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
            )ENGINE=InnoDB DEFAULT CHARSET=utf8;'
        );

        // Add Table 'Ticket' in database if not exist
        DB::get(MAINDB)->query(
            'CREATE TABLE IF NOT EXISTS ' . TicketComment::getTable() . ' (
                `id` INT(11) NOT NULL PRIMARY KEY AUTO_INCREMENT, 
                `ticketId` INT(11) NOT NULL,
                `description` TEXT,
                `author` INT(11) NOT NULL,
                `mtime` INT(11),    
                FOREIGN KEY (`ticketId`) REFERENCES ' . Ticket::getTable() . ' (`id`) ON DELETE CASCADE ON UPDATE CASCADE
            )ENGINE=InnoDB DEFAULT CHARSET=utf8;'
        );
    }

    /**
     * Uninstall the plugin. This method is called on plugin uninstallation, after it has been removed from the database
     */
    public function uninstall(){
        //Remove table from database
        DB::get(MAINDB)->query('DROP TABLE IF EXISTS ' . TicketComment::getTable());

        DB::get(MAINDB)->query('DROP TABLE IF EXISTS ' . Ticket::getTable());        
        
        DB::get(MAINDB)->query('DROP TABLE IF EXISTS ' . TicketProject::getTable());

    }

    /**
     * Activate the plugin. This method is called when the plugin is activated, just after the activation in the database
     */
    public function activate(){
        $permission = Permission::add(self::PLUGIN_NAME . '.manage-ticket', 0, 0);

        $menu = MenuItem::add(array(
            'plugin' => self::PLUGIN_NAME,
            'name' => 'main-ticket',
            'labelkey' => 'ticket.main-menu-title',
            'permissionId' => $permission->id
        )); 

        MenuItem::add(array(
            'plugin' => self::PLUGIN_NAME,
            'name' => 'project',
            'labelkey' => 'ticket.menu-project-title',
            'action' => 'ticket-project-index',
            'permissionId' => $permission->id,
            'parentId' => $menu->id,
        ));   


        MenuItem::add(array(
            'plugin' => self::PLUGIN_NAME,
            'name' => 'ticket',
            'labelkey' => 'ticket.menu-ticket-title',
            'action' => 'ticket-index',
            'permissionId' => $permission->id,
            'parentId' => $menu->id,
        ));     
    }
    
    /**
     * Deactivate the plugin. This method is called when the plugin is deactivated, just after the deactivation in the database
     */
    public function deactivate(){
        MenuItem::getByName(self::PLUGIN_NAME . '.main-ticket')->delete();

        MenuItem::getByName(self::PLUGIN_NAME . '.project')->delete();

        MenuItem::getByName(self::PLUGIN_NAME . '.ticket')->delete();

        Permission::getByName(self::PLUGIN_NAME . '.manage-ticket')->delete();
    }

    /**
     * Configure the plugin. This method contains a page that display the plugin configuration. To treat the submission of the configuration
     * you'll have to create another method, and make a route which action is this method. Uncomment the following function only if your plugin if 
     * configurable.
     */
    public function settings(){
        $param = array(
            'id' => 'ticket-settings-form', 
            'fieldsets' => array(
                'general' => array(
                    'nofieldset' => true,
                    new TextareaInput(array(
                        'name' => 'options',
                        'required' => true,
                        'label' => Lang::get('admin.profile-question-form-options-label'),
                        'labelClass' => 'required',
                        'attributes' => array(
                            'data-bind' => "value : options",
                        ),
                        'default' => implode(PHP_EOL, array_keys(json_decode(Option::get('ticket.status'), true))),
                        'cols' => 20,
                        'rows' => 10
                    ))
                ),          
                
                '_submits' => array(
                    new SubmitInput(array(
                        'name' => 'valid',
                        'value' => Lang::get('ticket.valid-button')
                    )),

                    new ButtonInput(array(
                        'name' => 'cancel',
                        'value' => Lang::get('ticket.cancel-button'),
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
                'title' => Lang::get('ticket.settings-form-title'),
                'icon' => 'cogs',
            ));
        }
        else{
            if($form->check()){                 
                $keys = array();
                foreach(explode(PHP_EOL, $form->getData("options")) as $i => $option){
                    if(!empty($option)){
                        $keys[trim($option)] = trim($option);
                    }
                }   

                Option::set('ticket.status', json_encode($keys));

                return $form->response(Form::STATUS_SUCCESS);       
            }
        }
    }
}