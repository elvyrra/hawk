<?php
/**
 * ViewPluginPanel.class.php
 * @author Elvyrra SAS
 */

/**
 * This class is used to display a panel in a view
 * @package View\Plugins
 */
class ViewPluginPanel extends ViewPlugin{
	/**
	 * The 'id' attribute of the panel
	 */
	public $id,

	/**
	 * The panel title
	 */
	$title,

	/**
	 * The panel title icon
	 */
	$icon,

	/**
	 * The panel content
	 */
	$content,

	/**
	 * The panel type : 'default', 'info', 'primary', 'success', 'warning', or 'danger'
	 */
	$type;

	/**
	 * Display the panel
	 * @return string the displayed HTML
	 */
	public function display(){
		if(!$this->id){
			$this->id = uniqid();
		}

		return View::make(ThemeManager::getSelected()->getView('panel.tpl'), array(
			'id' => $this->id,
			'title' => $this->title,
			'icon' => $this->icon,
			'content' => $this->content,
			'type' => $this->type ? $this->type : 'default'	
		));
	}
}