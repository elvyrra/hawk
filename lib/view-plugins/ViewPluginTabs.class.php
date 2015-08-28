<?php
/**
 * ViewPluginTabs.class.php
 * @author Elvyrra SAS
 */


/**
 * This class is used to display a tab system in a view
 * @package View\Plugins
 */
class ViewPluginTabs extends ViewPlugin{
	/**
	 * The 'id' attribute of the accordion
	 * @var string
	 */
	public $id,

	/**
	 * The defaultly selected panel
	 * @var string
	 */
	$selected,

	/**
	 * The tabs set
	 * @var array
	 */
	$tabs = array();

	/**
	 * Display the tabs
	 * @return string The displayed HTML
	 */
	public function display(){
		if(!$this->id){
			$this->id = uniqid();
		}
		foreach($this->tabs as &$tab){
			if(empty($tab['id'])){
				$tab['id'] = uniqid();
			}
		}
		if(!$this->selected){
			$this->selected = array_keys($this->tabs)[0];
		}

		return View::make(ThemeManager::getSelected()->getView('tabs.tpl'), array(
			'id' => $this->id,
			'tabs' => $this->tabs,
			'selected' => $this->selected
		));

	}
}