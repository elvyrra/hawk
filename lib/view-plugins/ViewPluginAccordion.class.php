<?php
/**
 * ViewPluginAccordion.class.php
 * @author Elvyrra SAS
 */


/**
 * This class is used to display an accordion in a view
 * @package View\Plugins
 */
class ViewPluginAccordion extends ViewPlugin{
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
	 * The panels set
	 * @var array
	 */
	$panels;


	/**
	 * Display the accordion
	 * @return string The displayed HTML
	 */
	public function display(){
		if(!$this->id){
			$this->id = uniqid();
		}

		foreach($this->panels as $name => &$panel){
			if(empty($panel['id'])){
				$panel['id'] = uniqid();
			}

			if(!isset($this->selected)){
				$this->selected = $name;
			}
		}

		return View::make(ThemeManager::getSelected()->getView('accordion.tpl'), array(
			'id' => $this->id,
			'panels' => $this->panels
		));
	}
}