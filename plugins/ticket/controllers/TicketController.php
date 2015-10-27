<?php

namespace Hawk\Plugins\Ticket;

class TicketController extends Controller{

	const PLUGIN_NAME = 'ticket';

	/**
	 * Entry point for the page of tickets
	 */
	public function index(){
		// Get list of all subject
		$list = $this->compute('getlistTicket');

		// Add css file
        $this->addCss(Plugin::current()->getCssUrl('ticket.less'));
        
        // Add javascript file
        $this->addJavaScript(Plugin::current()->getJsUrl('ticket.js'));

		return NoSidebarTab::make(array(
			'page' => $list,
			'title' => Lang::get('ticket.page-title'),
			'icon' => 'book'			
		));
    }  

    /**
     * Display the list of tickets
     */
    public function getlistTicket(){
    	$users = array_map(function($a){ return $a->username; }, User::getAll('id'));

    	$param = array(
			'id' => 'ticket-list',
			'model' => 'Ticket',
			'action' => Router::getUri('ticket-list'),
			'reference' => 'id',
			'controls' => array(
				array(
					'icon' => 'plus',
					'label' => Lang::get('ticket.new-ticket-btn'),
					'class' => 'btn-success',
					'href' => Router::getUri("ticket-editTicket", array('ticketId' => 0)),
				),

				array(
					'icon' => 'cubes',
					'label' => Lang::get('ticket.new-project-btn'),
					'class' => 'btn-primary',
					'href' => Router::getUri("ticket-editProject", array('projectId' => 0)),
					'target' => 'dialog',
				),			
			),
			'fields' => array(

				'actions' => array(
					'independant' => true,
					'display' => function($value, $field, $ticket){
						return "<i class='icon icon-pencil text-primary' href='". Router::getUri('ticket-editTicket', array('ticketId' => $ticket->id)) . "'></i>" .
							   "<i class='icon icon-close text-danger delete-ticket' data-ticket='{$ticket->id}'></i>";
					},
					'search' => false,
					'sort' => false,
				),

				// Project's name
				'projectId' => array(
					'label' => Lang::get('ticket.project-name-label'),
					'display' => function($value, $field, $ticket){
						return TicketProject::getById($value)->name;
					},
				),

				'title' => array(
					'label' => Lang::get('ticket.title-label'),
				),

				'description' => array(
					'label' => Lang::get('ticket.description-label'),
					'display' => function($value){
						$maxLength = 150;
						$value = strip_tags($value);
						if(strlen($value) > $maxLength){
							return substr($value, 0, $maxLength - 4) . ' ...';
						}
						else{
							return $value;
						}
					}
				),

				'status' => array(
					'label' => Lang::get('ticket.status-label'),
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

				'target' => array(
					'label' => Lang::get('ticket.target-label'),
					'display' => function($value, $field, $ticket){
						return User::getById($value)->username;
					},
					'search' => array(
						'type' => 'select',
						'invitation' => ' - ',
						'options' => $users,
					)
				),

				'deadLine' => array(
					'label' => Lang::get('ticket.deadLine-label'),
					'display' => function($value, $field){

       					if(empty($value)){
       						return '<span class="text-danger"> - </span>';
       					}

       					if(strtotime($value) < strtotime(date('Y-m-d'))){
       						return '<span class="text-danger">' . strftime(Lang::get('ticket.format-date-label') ,strtotime($value)) . '</span>';
       					}
       					else{
							return '<span class="text-success">' . strftime(Lang::get('ticket.format-date-label') ,strtotime($value)) . '</span>';
       					}
					},			
				),

				'mtime' => array(
					'label' => Lang::get('ticket.mtime-label'),
					'display' => function($value, $field){
                        return date(Lang::get('main.time-format'), $value);
                    },
                    'search' => false,
				),
			)
		);

		$list = new ItemList($param);

		if(Request::getParams('refresh') ){
			return $list->display();	
		}
		else{
			return View::make(Plugin::current()->getView("ticket-list.tpl"), array(
				'list' => $list,
			));
		}
    }

    /**
     * Edit a ticket
     */
	public function editTicket(){

		// Options select
		$projects = array_map(function($a){ return $a->name; }, TicketProject::getAll('id'));
		$users = array_map(function($a){ return $a->username; }, User::getAll('id'));

		$status = json_decode(Option::get('ticket.status'));
		$options = array();

		foreach($status as $stat){
			$options[$stat] = $stat;
		}

		$param = array(
			'id' => 'ticket-form',		
			'model' => 'Ticket',
			'reference' => array('id' => $this->ticketId),
			'fieldsets' => array(
				'general' => array(
					'nofieldset' => true,
					
					new SelectInput(array(
						'name' => 'projectId',
						'options' => $projects,
						'label' => Lang::get('ticket.form-ticket-project-label')
					)),

					new TextInput(array(
						'name' => 'title',
						'required' => true,
						'label' => Lang::get('ticket.title-label'),
					)),

					new WysiwygInput(array(
						'name' => 'description',
						'label' => Lang::get('ticket.description-label'),
					)),

					new SelectInput(array(
						'name' => 'status',
						'options' => $options,
						'label' => Lang::get('ticket.status-label')
					)),
					
					new SelectInput(array(
						'name' => 'target',
						'options' => $users,
						'label' => Lang::get('ticket.target-label')
					)),

					new DatetimeInput(array(
						'name' => 'deadLine',
						'label' => Lang::get('ticket.deadLine-label'),
						'value' => date('Y-m-d'),
					)),

					new HiddenInput(array(
						'name' => 'author',
						'value' => Session::getUser()->id,
					)),
					
					new HiddenInput(array(
						'name' => 'mtime',
						'value' => time(),
					)),
				),	
				
				'_submits' => array(
					new SubmitInput(array(
						'name' => 'valid',
						'value' => Lang::get('main.valid-button')
					)),

					new DeleteInput(array(
						'name' => 'delete',
						'value' => Lang::get('main.delete-button'),
						'notDisplayed' => ! $this->ticketId
					)),

					new ButtonInput(array(
						'name' => 'cancel',
						'value' => Lang::get('main.cancel-button'),
						'onclick' => 'app.load(app.getUri("ticket-index"))'
					))
				),
			),
			'onsuccess' => 'app.load(app.getUri("ticket-index"));'
		);

		$form = new Form($param);


		if(!$form->submitted()){			

			$display = View::make(Plugin::current()->getView("ticket-form.tpl"), array(
				'form' => $form,
				'history' => $this->compute('history'),
			));

			return NoSidebarTab::make(array(
				'page' => $display,
				'title' => Lang::get('ticket.ticket-form-title'),
				'icon' => 'book'			
			));
		}
		else{
			if($form->submitted() === "delete"){
				return $form->delete();
			}
			elseif($form->check()){   
				$oldValues = Ticket::getById($this->ticketId);

				if($oldValues){
					$comments = array();
					foreach(array('title', 'status', 'description', 'target', 'deadLine') as $key){
						if($oldValues->$key !== $form->fields[$key]->dbvalue()){
							$comments[] = Lang::get('ticket.' . $key . '-change-comment', array('oldValue' => $oldValues->$key, 'newValue' => $form->getData($key)));
						}
					}

					if(!empty($comments)){
						TicketComment::add(array(
							'author' => Session::getUser()->id,
							'ticketId' => $this->ticketId,
							'mtime' => time(),
							'description' => implode('<br />', $comments)
						));
					}
				}

				$form->register(Form::NO_EXIT);

				return $form->response(Form::STATUS_SUCCESS);
			}			
		}
	}

	/**
	 * Remove a ticket
	 */
	public function removeTicket(){
		$ticket = Ticket::getById($this->id);		
		$ticket->delete();
	}


	/**
	 * Display the list of comments for a ticket
	 */
	public function history(){
		$paramList = array(
			'id' => 'ticket-history',
			'model' => 'TicketComment',
			'action' => Router::getUri('ticket-history', array('ticketId' => $this->ticketId)),
		    'filter' => new DBExample(array('ticketId' => $this->ticketId)),
		    'controls' => $this->ticketId ? array(
				array(
					'icon' => 'plus',
					'label' => Lang::get('ticket.new-comment-btn'),
					'class' => 'btn-success',
					'href' => Router::getUri("ticket-editComment", array('ticketId' => $this->ticketId, 'commentId' => 0)),
					'target' => 'dialog',
				) ,
			) : array(),
			'fields' => array(
				'description' => array(
					'label' => Lang::get('ticket.description-label'),
				),

				'author' => array(
					'label' => Lang::get('ticket.author-label'),
					'display' => function($value, $field, $ticket){
						return User::getById($value)->username;
					},
				),

				'mtime' => array(
					'label' => Lang::get('ticket.history-mtime-label'),
					'display' => function($value){
                        return date(Lang::get('main.time-format'), $value);
                    },
                    'search' => false,
				),
			)
		);

		$list = new ItemList($paramList);

		return $list->display();
	}



    /**
     * Edit a ticket comment
     */
	public function editComment(){

		$param = array(
			'id' => 'ticket-form-comment',		
			'model' => 'TicketComment',
			'reference' => array('id' => $this->commentId),
			'fieldsets' => array(
				'general' => array(
					'nofieldset' => true,
					
					new WysiwygInput(array(
						'name' => 'description',
						'id' => 'home-page-html',
					)),

					new HiddenInput(array(
						'name' => 'ticketId',
						'value' => $this->ticketId,
					)),

					new HiddenInput(array(
						'name' => 'author',
						'value' => Session::getUser()->id,
					)),
					
					new HiddenInput(array(
						'name' => 'mtime',
						'value' => time(),
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
			'onsuccess' => 'app.dialog("close");'//app.lists["ticket-form-main"].refresh();'
		);

		$form = new Form($param);
		
		if(!$form->submitted()){
			return View::make(Theme::getSelected()->getView("dialogbox.tpl"), array(
				'page' => $form,
				'title' => Lang::get('ticket.ticket-comment-form-title'),
			));
		}
		else{
			return $form->treat();			
		}
	}

	/*** Edit settings for this Plugins	***/
	public function settings(){
		$param = array(
			'id' => 'ticket-settings-form',	
			'model' => '\Hawk\Plugins\Ticket\TicketOption',
			'reference' => array('plugin' => 'ticket'),
			'fieldsets' => array(
				'general' => array(
					'nofieldset' => true,
					new TextareaInput(array(
						'name' => 'options',
						'independant' => true,
						'label' => Lang::get('admin.profile-question-form-options-label'),
						'labelClass' => 'required',
						'attributes' => array(
							'data-bind' => "value : options",
						),
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
			//return $form->treat();	
			if($form->submitted() == "delete"){
				$this->compute('delete');					

				return $form->response(Form::STATUS_SUCCESS);
			}
			else{
				if($form->check()){					
					$form->register(Form::NO_EXIT);
					$keys = array();
					foreach(explode(PHP_EOL, $form->getData("options")) as $i => $option){
						if(!empty($option)){
							$keys[$option] = trim($option);
							//Log::debug(trim($option));
						}
					}	

					//Log::debug(var_dump($keys));
					Log::debug(json_encode($keys));
					Option::set('ticket.status', json_encode($keys));

					return $form->response(Form::STATUS_SUCCESS);		
				}
			}		
		}
	}
}