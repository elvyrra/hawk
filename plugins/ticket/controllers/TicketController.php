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

		return LeftSidebarTab::make(array(
			'page' => array(
				'content' => $list
			),
			'sidebar' => array(
				'widgets' => array(new TicketFilterWidget())
			),
			'title' => Lang::get('ticket.page-title'),
			'icon' => 'book',
			'tabId' => 'tickets-page'			
		));


    }  

    /**
     * Display the list of tickets
     */
    public function getlistTicket(){
    	$users = array_map(function($a){ return $a->username; }, User::getAll('id'));
    	$projects = array_map(function($a){ return $a->name; }, TicketProject::getAll('id'));

    	$filters = TicketFilterWidget::getInstance()->getFilters();
    	$filter = null;
    	if(!empty($filters['status'])){
    		$filter = new DBExample(array(
    			'status' => array('$in' => array_keys($filters['status']))
    		));
    	}

    	$param = array(
			'id' => 'ticket-list',
			'model' => 'Ticket',
			'action' => App::router()->getUri('ticket-list'),			
			'filter' => $filter,
			'reference' => 'id',
			"lineClass" => function($line){
				return "danger";
			},
			'controls' => array(
				array(
					'icon' => 'plus',
					'label' => Lang::get('ticket.new-ticket-btn'),
					'class' => 'btn-success',
					'href' => App::router()->getUri("ticket-editTicket", array('ticketId' => 0)),
				),

				array(
					'icon' => 'cubes',
					'label' => Lang::get('ticket.new-project-btn'),
					'class' => 'btn-primary',
					'href' => App::router()->getUri("ticket-editProject", array('projectId' => 0)),
					'target' => 'dialog',
				),			
			),
			'fields' => array(
				'actions' => array(
					'independant' => true,
					'display' => function($value, $field, $ticket){
						return "<i class='icon icon-pencil text-primary' href='". App::router()->getUri('ticket-editTicket', array('ticketId' => $ticket->id)) . "'></i>" .
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
					'search' => array(
						'type' => 'select',
						'options' => $projects,
						'invitation' => ' - '
					),
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
					'search' => false
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
       						return '<span class="text-danger">' . date(Lang::get('main.date-format'), strtotime($value)) . '</span>';
       					}
       					else{
							return '<span class="text-success">' . date(Lang::get('main.date-format'), strtotime($value)) . '</span>';
       					}
					},	
					'search' => array(
						'type' => 'date'
					)		
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

		Lang::addKeysToJavaScript('ticket.delete-ticket-confirmation');

		return $list->display();	
    }

    /**
     * Edit a ticket
     */
	public function editTicket(){

		// Options select
		$projects = array_map(function($a){ return $a->name; }, TicketProject::getAll('id'));
		$users = array_map(function($a){ return $a->username; }, User::getAll('id'));

		$options = json_decode(Option::get('ticket.status'));

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
						'value' => App::session()->getUser()->id,
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
					foreach(array('title', 'description', 'deadLine') as $key){
						if($oldValues->$key !== $form->getData($key)){  //$form->fields['status']->dbvalue()
							$comments[] = Lang::get('ticket.' . $key . '-change-comment', array('oldValue' => $oldValues->$key, 'newValue' => $form->getData($key)));
						}
					}

					if($oldValues->status !== $form->getData('status')){
						$oldValue = $oldValues->status;
						$newValue = $form->getData('status');
						$comments[] = Lang::get('ticket.status-change-comment', array('oldValue' => $options->$oldValue, 'newValue' => $options->$newValue));
					}

					if($oldValues->target !== $form->getData('target')){
						$oldValue = $oldValues->target;
						$newValue = $form->getData('target');
						$comments[] = Lang::get('ticket.target-change-comment', array('oldValue' => $users[$oldValue], 'newValue' => $users[$newValue]));
					}

					if(!empty($comments)){
						TicketComment::add(array(
							'author' => App::session()->getUser()->id,
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
		$ticket = Ticket::getById($this->ticketId);		
		$ticket->delete();
	}


	/**
	 * Display the list of comments for a ticket
	 */
	public function history(){
		$paramList = array(
			'id' => 'ticket-history',
			'model' => 'TicketComment',
			'action' => App::router()->getUri('ticket-history', array('ticketId' => $this->ticketId)),
		    'filter' => new DBExample(array('ticketId' => $this->ticketId)),
		    'controls' => $this->ticketId ? array(
				array(
					'icon' => 'plus',
					'label' => Lang::get('ticket.new-comment-btn'),
					'class' => 'btn-success',
					'href' => App::router()->getUri("ticket-editComment", array('ticketId' => $this->ticketId, 'commentId' => 0)),
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
						'value' => App::session()->getUser()->id,
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
						}
					}	

					App::logger()->debug(json_encode($keys));
					Option::set('ticket.status', json_encode($keys));

					return $form->response(Form::STATUS_SUCCESS);		
				}
			}		
		}
	}
}