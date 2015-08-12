<?php
/**
 * Controller.class.php
 * @author Elvyrra SAS
 */
	

/**
 * This class describes the behavior of a controller. All controllers defined in application plugins 
 * must extend this class for the application routes work.
 * @package Core
 */
class Controller{	
	/**
	 * The current used controller. This static property is used to know which is the current controller associated to the current route
	 */
	public static $currentController = null;
	
	const BEFORE_ACTION = 'before';
    
    const AFTER_ACTION = 'after';
	
	/**
	 * Constructor
	 * @param array $param The parameters of the controller. This parameter is set by the router with the parameters defined in the routes as '{paramName}'
	 */
	public function __construct($param = array()){		
		foreach($param as $key => $value){
			$this->$key = $value;
		}	
		$this->theme = ThemeManager::getSelected();
		self::$currentController = $this;
	}
	

	/**
	 * Get a controller instance
	 * @param array $param The parameters to send to the controller instance
	 * @return Controller The controller instance
	 */
	public static function getInstance($param = array()){
		return new static($param);		
	}
	

	/**
	 * Execute a controller method. This method is called by the router. 
	 * It execute the controller method, and triggers events before and after the method has been executed, to add widgets or other functionnalities
	 * from another plugin than the controller's one.
	 * @param string $method The method to execute
	 * @param mixed The result of the controller method execution
	 */
	public function compute($method){
		/*** Load widgets before calling the controller method ***/
		EventManager::trigger(new Event(get_called_class() . '.' . $method . '.' . self::BEFORE_ACTION, array('controller' => $this)));
		
		/*** Call the controller method ***/
		$result = $this->$method();
		if(Response::getType() == 'html'){
			// Create a phpQuery object to be modified by event listeners (widgets)
			$dom = phpQuery::newDocument($result);
		}
		else{			
			$dom = $result;
		}
				
		/*** Load the widgets after calling the controller method ***/		
		$event = new Event(get_called_class() . '.' . $method . '.' . self::AFTER_ACTION, array('controller' => $this, 'result' => $dom));
		EventManager::trigger($event);
		
		$result = $event->getData('result');
		
		if( $result instanceof phpQuery){			
			return $result->htmlOuter();
		}
		else{
			return $result;
		}
	}


	/**
	 * Add a link tag for CSS inclusion at the end of the HTML result to return to the client
	 * @param string $url The URL of the css file to load
	 */
	public function addCss($url){
		Widget::add(Router::getCurrentAction(), Controller::AFTER_ACTION, function($event) use($url){
			pq("*:last")->after("<link rel='stylesheet' property='stylesheet' type='text/css' href='$url' />");
		});
	}

	/**
	 * Add inline CSS at the end of the HTML result to return to the client
	 * @param string $style The CSS code to insert
	 */
	public function addCssInline($style){
		Widget::add(Router::getCurrentAction(), Controller::AFTER_ACTION, function($event) use($style){
			pq("*:last")->after("<style type='text/css'>$style</style>");
		});
	}

	/**
	 * Add a script tag at the end of the HTML result to return to the client
	 * @param string $url The URL of the JavaScript file to load
	 */
	public function addJavaScript($url){
		Widget::add(Router::getCurrentAction(), Controller::AFTER_ACTION, function($event) use($url){
			pq("*:last")->after("<script type='text/javascript' src='$url'></script>");
		});
	}


	/**
	 * Add inline JavaScript code at the end of the HTML result to return to the client
	 * @param string $script The JavaScript code to insert
	 */
	public function addJavaScriptInline($script){
		Widget::add(Router::getCurrentAction(), Controller::AFTER_ACTION, function($event) use($script){
			pq("*:last")->after("<script type='text/javascript'>$script</script>");
		});	
	}
}