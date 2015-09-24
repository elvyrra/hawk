<?php
/**
 * Accordion.class.php
 * @author Elvyrra SAS
 */

namespace Hawk\View\Plugins;

/**
 * This class is used to display an accordion in a view
 * @package View\Plugins
 */
class Accordion extends \Hawk\ViewPlugin{
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

		return \Hawk\View::make(\Hawk\ThemeManager::getSelected()->getView('accordion.tpl'), array(
			'id' => $this->id,
			'panels' => $this->panels
		));
	}
}