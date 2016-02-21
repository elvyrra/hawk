<?php
/**
 * Panel.php
 *
 * @author  Elvyrra SAS
 * @license http://rem.mit-license.org/ MIT
 */

namespace Hawk\View\Plugins;

/**
 * This class is used to display a panel in a view
 *
 * @package View\Plugins
 */
class Panel extends \Hawk\ViewPlugin{
    /**
     * The 'id' attribute of the panel
     */
    public $id,

    /**
     * The panel title
     *
     * @var string
     */
    $title,

    /**
     * The panel title icon
     *
     * @var string
     */
    $icon,

    /**
     * The panel content
     *
     * @var string
     */
    $content,

    /**
     * The panel type : 'default', 'info', 'primary', 'success', 'warning', or 'danger'
     *
     * @var string
     */
    $type;

    /**
     * Display the panel
     *
     * @return string the displayed HTML
     */
    public function display(){
        if(!$this->id) {
            $this->id = uniqid();
        }

        return \Hawk\View::make(
            \Hawk\Theme::getSelected()->getView('panel.tpl'), array(
            'id' => $this->id,
            'title' => $this->title,
            'icon' => $this->icon,
            'content' => $this->content,
            'type' => $this->type ? $this->type : 'default'
            )
        );
    }
}
