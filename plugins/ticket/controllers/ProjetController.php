<?php

namespace Hawk\Plugins\Ticket;

class ProjetController extends Controller{

	const PLUGIN_NAME = 'ticket';

	/*** Entry point of the page ticket ***/
	public function index(){
		// Get list of all subject
		$list = $this->compute('getlistProject');

		// Add css file
        $this->addCss(Plugin::current()->getCssUrl("ticket.less"));
        
        // Add javascript file
        $this->addJavaScript(Plugin::current()->getJsUrl('ticket.js'));

        Lang::addKeysToJavaScript('ticket.delete-project-confirmation');

		return NoSidebarTab::make(array(
			'page' => $list,
			'title' => Lang::get('ticket.project-page-title'),
			'icon' => 'book'			
		));
    }  

	/*** Get all main ticket from fbsql_database ***/
    public function getlistProject(){

    	$param = array(
			'id' => 'ticket-project-list',
			'model' => 'TicketProject',
			'action' => App::router()->getUri('ticket-project-list'),			
			'controls' => array(
				array(
					'icon' => 'plus',
					'label' => Lang::get('ticket.new-project-btn'),
					'class' => 'btn-success',
					'href' => App::router()->getUri("ticket-editProject", array('projectId' => 0)),
					'target' => 'dialog',
				),
				array(
					'icon' => 'eye',
					'label' => Lang::get('ticket.view-ticket-btn'),
					'class' => 'btn-primary',
					'href' => App::router()->getUri("ticket-index"),
					'target' => 'newtab',
				),
			),
			'fields' => array(
				'actions' => array(
					'independant' => true,
					'display' => function($value, $field, $project){
						return "<i class='icon icon-pencil text-primary' href='". App::router()->getUri('ticket-editProject', array('projectId' => $project->id)) . "' target='dialog'></i>" .
							   "<i class='icon icon-close text-danger delete-project' data-project='{$project->id}'></i>";
					},
					'search' => false,
					'sort' => false,
				),
			
				'name' => array(
					'label' => Lang::get('ticket.project-name-label'),
				),

				'description' => array(
					'label' => Lang::get('ticket.project-description-label'),
				),

				'status' => array(
					'label' => Lang::get('ticket.project-status-label'),
					'search' => array(
						'type' => 'select',
						'options' => call_user_func(function(){
							$status = json_decode(Option::get('ticket.status'));
							$options = array();

							foreach($status as $stat){
								$options[$stat] = $stat;
							}
							return $options;
						}),	
						'invitation' => Lang::get('ticket.project-status-all')
					),
				),

				'author' => array(
					'label' => Lang::get('ticket.author-label'),
					'display' => function($value, $field, $ticket){
						return User::getById($value)->username;
					},
				),

				'mtime' => array(
					'label' => Lang::get('ticket.project-mtime-label'),
					'display' => function($value, $field){
						return date(Lang::get('main.date-format'), $value);
					},
					'search' => false,
				),
			)
		);

		$list = new ItemList($param);

		if(App::request()->getParams('refresh') ){
			return $list->display();	
		}
		else{
			return View::make(Plugin::current()->getView("project-list.tpl"), array(
				'list' => $list,
			));
		}
    }

    /*** Edit a project 	***/
    public function editProject(){

    	$status = json_decode(Option::get('ticket.status'));	
    	$options = array();

		foreach($status as $stat){
			$options[$stat] = $stat;
		}
		$param = array(
			'id' => 'ticketProject-form',	
			'model' => 'TicketProject',
			'reference' => array('id' => $this->projectId),
			'fieldsets' => array(
				'general' => array(
					
					new TextInput(array(
						'name' => 'name',
						'required' => true,
						'label' => Lang::get('ticket.project-name-label'),
					)),

					new WysiwygInput(array(
						'name' => 'description',
						'label' => Lang::get('ticket.project-description-label'),
						'labelWidth' => 'auto',
						'attributes' => array('ko-wysiwyg' => '1'),
					)),
				
					new SelectInput(array(
						'name' => 'status',
						'options' => $options,
						'label' => Lang::get('ticket.status-label')
					)),

					new HiddenInput(array(
						'name' => 'mtime',
						'value' => date('Y-m-d'),
					)),

					new HiddenInput(array(
						'name' => 'author',
						'value' => App::session()->getUser()->id,
					)),

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
			'onsuccess' => 'app.dialog("close");if(app.lists["ticket-project-list"]){app.lists["ticket-project-list"].refresh();}'
		);

		$form = new Form($param);
		
		if(!$form->submitted()){
			return View::make(Theme::getSelected()->getView("dialogbox.tpl"), array(
				'page' => $form,
				'title' => Lang::get('ticket.project-form-title'),
				'icon' => 'book',
			));
		}
		else{
			return $form->treat();			
		}
	}

	/*** Remove a project ***/ 
	public function removeProject(){
		$project = TicketProject::getById($this->projectId);		
		$project->delete();
	}
}