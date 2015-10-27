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
            "CREATE TABLE IF NOT EXISTS `" . (string) Conf::get('db.prefix') . "TicketProject` (
            `id` int NOT NULL AUTO_INCREMENT,
            `name` VARCHAR(32) NOT NULL UNIQUE,
            `description` TEXT NOT NULL,
            `author` int NOT NULL,
            `status` VARCHAR(32) NOT NULL,
            `mtime` INT(11),    
            PRIMARY KEY (`id`)
            )ENGINE=InnoDB DEFAULT CHARSET=utf8;"
        );

        // Add Table 'Ticket' in database if not exist
        DB::get(MAINDB)->query("CREATE TABLE IF NOT EXISTS `" . (string) Conf::get('db.prefix') . "Ticket` (
            `id` int NOT NULL AUTO_INCREMENT, 
            `projectId` int NOT NULL,
            `title` VARCHAR(256) NOT NULL UNIQUE,
            `description` TEXT NOT NULL,
            `status` VARCHAR(32) NOT NULL,
            `author` int NOT NULL,
            `target` int NOT NULL,
            `deadLine` DATE,
            `mtime` int(11),    
            PRIMARY KEY (`id`),
            FOREIGN KEY (`projectId`) REFERENCES " . (string) Conf::get('db.prefix') . "TicketProject (`id`)
            )ENGINE=InnoDB DEFAULT CHARSET=utf8;"
        );

        // Add Table 'Ticket' in database if not exist
        DB::get(MAINDB)->query("CREATE TABLE IF NOT EXISTS `" . (string) Conf::get('db.prefix') . "TicketComment` (
            `id` int NOT NULL AUTO_INCREMENT, 
            `ticketId` int NOT NULL,
            `description` TEXT,
            `author` INT(11) NOT NULL,
            `mtime` int(11),    
            PRIMARY KEY (`id`),
            FOREIGN KEY (`ticketId`) REFERENCES " . (string) Conf::get('db.prefix') . "Ticket (`id`) ON DELETE CASCADE ON UPDATE CASCADE
            )ENGINE=InnoDB DEFAULT CHARSET=utf8;"
        );
    }

    /**
     * Uninstall the plugin. This method is called on plugin uninstallation, after it has been removed from the database
     */
    public function uninstall(){
        //Remove table from database
        DB::get(MAINDB)->query("DROP TABLE IF EXISTS `" . (string) Conf::get('db.prefix') . "TicketProject`");

        DB::get(MAINDB)->query("DROP TABLE IF EXISTS `" . (string) Conf::get('db.prefix') . "Ticket`");

        DB::get(MAINDB)->query("DROP TABLE IF EXISTS `" . (string) Conf::get('db.prefix') . "TicketComment`");
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
        return TicketController::getInstance()->settings();
    }
}