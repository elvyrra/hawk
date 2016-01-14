<?php

namespace Hawk\Plugins\FileManager;

class FileManagerController extends Controller{

	const PLUGIN_NAME = 'fileManager';

	/**
	 * Entry point for the page of fileManager
	 */
	public function index(){
		// Get list of all subject
		$tree = $this->getMainTree();
		$preview = $this->getPreview();

		// Add css file
	    $this->addCss(Plugin::current()->getCssUrl('fileManager.less'));
	        
	    // Add javascript file
		$this->addJavaScript(Plugin::current()->getJsUrl('jquery.media.js'));
	    $this->addJavaScript(Plugin::current()->getJsUrl('fileManager.js'));
	
		return View::make(Plugin::current()->getView('fileManager.tpl'), array(
			'page' => array(
				'content' => $preview,
				'class' => 'col-md-9 col-lg-8'
			),
			'sidebar' => array(
				'content' => $tree,
				'class' => 'col-md-3 col-lg-4'
			),
			'title' => Lang::get('fileManager.page-title'),
			'icon' => 'file',
			'tabId' => 'fileManager-page'			
		));
  	}  
	
	/*
	* Get Main tree for ROOT_FILE_MANAGER_DIRECTORY
	*/
	public function getPreview(){
    	return View::make(Plugin::current()->getView('preview.tpl')); 
 	}

	/*
	* Get Main tree for ROOT_FILE_MANAGER_DIRECTORY
	*/
	public function getMainTree(){

		if(! is_dir(Plugin::current()->getPublicUserfilesDir() . 'Documents')){						
			mkdir(Plugin::current()->getPublicUserfilesDir() . 'Documents', 0775, true);					
		}

		$this->idElement = 0;
    	// Scan temporary directory and check if it's contains dir with plugin name
    	$tree = "<ol class='main-tree sortable active' data-id='" . $this->idElement . "'>";
    	$tree = $tree . "<li><span><i class='icon icon-folder-open icon-3x'></i>Documents</span>";
    	$this->idElement++;

    	$tree = $tree . "<a target='dialog' href='" . App::router()->getUri("fileManager-editRootFolder") . "'><i class='icon icon-edit icon-lg'></i></a>";
    	$tree = $tree . $this->scanFolder(Plugin::current()->getPublicUserfilesDir() . 'Documents');
    	$tree = $tree . "</li></ol>";

    	return View::make(Plugin::current()->getView('tree.tpl'), array(
			'tree' => $tree
		)); 
    }

    /*
	* Get URL of selected file to display or download it
	*/
    public function getUrlFile($basename){
    	return Plugin::current()->getUserfilesUrl() . $basename;
    }

	/*
	* Scan folder and extract file and folder to build tree
	*/
	public function scanFolder($dir) {
   		$result = "<ul>";
		
   		$cdir = scandir($dir); 
		$path = str_replace(Plugin::current()->getPublicUserfilesDir(), "", $dir);
		$path = Plugin::current()->getUserfilesUrl() . $path;
   		
   		foreach ($cdir as $key => $value){ 
	      	if (!in_array($value,array(".",".."))){ 
	         
		        if (is_dir($dir . DIRECTORY_SEPARATOR . $value)){
		        	
		        	$result = $result . "<li><span class='sortable-item' data-id='" . $this->idElement . "'><i class='icon icon-folder-open icon-3x'></i>" .  $value . "</span>";
		        	$result = $result . "<i class='icon icon-edit icon-lg edit-folder pointer' data-path='" . $dir . "' data-folder='" . $value . "' ></i>";
					$this->idElement++;
					// recusrive
		          	$result = $result . $this->scanFolder($dir . DIRECTORY_SEPARATOR . $value); 
		          	$result = $result . "</li>";
		        } 
		        else{ 
					$result = $result . "<li><span class='sortable-item preview-file' data-id='" . $this->idElement . "' data-path='" . $path . DIRECTORY_SEPARATOR . $value . "'><i class='icon icon-file icon-3x' data-path='" . $path . DIRECTORY_SEPARATOR . $value . "'></i>" . $value . "</span>";
					$result = $result . "<i class='icon icon-edit icon-lg edit-file pointer' data-path='" . $dir . "' data-file='" . $value . "' ></i>";
					$result = $result . "<a target='_blank' href='" . $path . DIRECTORY_SEPARATOR . $value . "' ><i class='icon icon-download icon-lg' ></i></a>";
					$result = $result . "</li>";
					$this->idElement++;
				} 
			} 
   		} 

   		$result = $result . "</ul>";
   		return $result; 
	} 

	/*
	* Edit Root Folder: Documents
	*/
	public function editRootFolder(){
		
		$param = array(
			'id' => 'fileManager-editFolder',
			'fieldsets' => array(
				'general' => array(

					new RadioInput(array(
						'name' => 'typeAction',
						'label' => Lang::get(self::PLUGIN_NAME . '.add-type-action'),
						'options' => array(
							'addFolder' => Lang::get('fileManager.addFolder-label'),
							'importFile' => Lang::get('fileManager.importFile-label'),
						),
						'default' => 'addFolder',
						'layout' => 'vertical',
						'attributes' => array(
							'ko-checked' => 'editFolder.type'
						)
					)),
					
					new TextInput(array(
						'name' => 'new-name',
						'required' => true,
						'label' => Lang::get(self::PLUGIN_NAME . '.new-name-folder-label'),
					)),
								
					new FileInput(array(
						'name' => 'file',
						'label' => Lang::get(self::PLUGIN_NAME . '.file-label'),
						'independant' => true,
					)),
				),
				
				'_submits' => array(
					new SubmitInput(array(
						'name' => 'valid',
						'icon' => 'save',
						'value' => Lang::get(self::PLUGIN_NAME . '.edit-submit-value'),						
					)),
				),
			),
			'onsuccess' => 'app.dialog("close");app.load(app.getUri("fileManager-index"));'
		); 

		// Get form add new plugin
		$form = new Form($param);
		
		if(!$form->submitted()){
			$this->addCss(Plugin::current()->getCssUrl('fileManager.less'));
    		$this->addJavaScript(Plugin::current()->getJsUrl('formEdit.js'));
			
			return View::make(Plugin::current()->getView('editFolder.tpl'), array(
				'title' => Lang::get(self::PLUGIN_NAME . '.editFolder-title'),
				'icon' => 'edit',
      			'form' => $form
			)); 
		}
		elseif($form->check()){

			if($form->getData('typeAction') == "addFolder"){
				mkdir(Plugin::current()->getPublicUserfilesDir() . 'Documents/' . $form->getData('new-name'), 0775, true);
			}
			else if($form->getData('typeAction') == "importFile"){
				$upload = Upload::getInstance('file');
				if($upload){
					$file = $upload->getFile(0);
					$extension = $file->extension;

					if(strstr($form->getData('new-name'), '.' . $extension) === false)
						 $upload->move($file, Plugin::current()->getPublicUserfilesDir() . 'Documents/', $form->getData('new-name') . '.' . $extension);
					else
						$upload->move($file, Plugin::current()->getPublicUserfilesDir() . 'Documents/', $form->getData('new-name'));
				}
				else{
					$form->error('file', Lang::get(self::PLUGIN_NAME . '.no-file-error'));
          			return $form->response(Form::STATUS_CHECK_ERROR, Lang::get(self::PLUGIN_NAME . '.no-file-error'));
				}
			}
			else{

			}

			return $form->response(Form::STATUS_SUCCESS);
		}
  	}
	
	/*
	* Edit folder
	*/
	public function editFolder(){
		$path = App::request()->getParams('path');
		$folder = App::request()->getParams('folder');
		
		$param = array(
			'id' => 'fileManager-editFolder',
			'fieldsets' => array(
				'general' => array(

					new RadioInput(array(
						'name' => 'typeAction',
						'label' => Lang::get(self::PLUGIN_NAME . '.add-type-action'),
						'options' => array(
							'removeFolder' => Lang::get('fileManager.removeFolder-label'),
							'editNameFolder' => Lang::get('fileManager.editNameFolder-label'),
							'addFolder' => Lang::get('fileManager.addFolder-label'),
							//'addFile' => Lang::get('fileManager.addFile-label'),
							'importFile' => Lang::get('fileManager.importFile-label'),
						),
						'default' => 'editNameFolder',
						'layout' => 'vertical',
						'attributes' => array(
							'ko-checked' => 'editFolder.type'
						)
					)),
					
					new HtmlInput(array(
						'name' => 'alert-remove',
						'value' => '<div class="alert alert-info"><br>' . Lang::get(self::PLUGIN_NAME . '.alert-remove-text') . '</br></div>'
					)),
					
					new TextInput(array(
						'name' => 'old-name',
						'value' => $folder,
						'readonly' => true,
						'label' => Lang::get(self::PLUGIN_NAME . '.old-name-folder-label'),
					)),
					
					new TextInput(array(
						'name' => 'new-name',
						'label' => Lang::get(self::PLUGIN_NAME . '.new-name-folder-label'),
					)),
					
					new TextInput(array(
						'name' => 'new-name-file',
						'label' => Lang::get(self::PLUGIN_NAME . '.new-name-file-label'),
					)),
					
					new FileInput(array(
						'name' => 'file',
						'label' => Lang::get(self::PLUGIN_NAME . '.file-label'),
						'independant' => true,
					)),
				),
				
				'_submits' => array(
					new SubmitInput(array(
						'name' => 'valid',
						'icon' => 'save',
						'value' => Lang::get(self::PLUGIN_NAME . '.edit-submit-value'),						
					)),
				),
			),
			'onsuccess' => 'app.dialog("close");app.load(app.getUri("fileManager-index"));'
		); 

		// Get form add new plugin
		$form = new Form($param);
		
		if(!$form->submitted()){
			$this->addCss(Plugin::current()->getCssUrl('fileManager.less'));
    		$this->addJavaScript(Plugin::current()->getJsUrl('formEdit.js'));
			
			return View::make(Plugin::current()->getView('editFolder.tpl'), array(
				'title' => Lang::get(self::PLUGIN_NAME . '.editFolder-title'),
				'icon' => 'edit',
      			'form' => $form
			)); 
		}
		elseif($form->check()){
			// Remove Folder
			if($form->getData('typeAction') == "removeFolder"){
				shell_exec('rm -r ' . $path . DIRECTORY_SEPARATOR . $folder);
			}
			else if($form->getData('typeAction') == "editNameFolder"){
				rename($path . DIRECTORY_SEPARATOR . $folder, $path . DIRECTORY_SEPARATOR . $form->getData('new-name'));
			}
			else if($form->getData('typeAction') == "addFolder"){
				mkdir($path . DIRECTORY_SEPARATOR . $folder . DIRECTORY_SEPARATOR . $form->getData('new-name'), 0775, true);
			}
			else if($form->getData('typeAction') == "addFile"){

			}
			else if($form->getData('typeAction') == "importFile"){
				$upload = Upload::getInstance('file');
				if($upload){
					$file = $upload->getFile(0);
					$extension = $file->extension;

					if(strstr($form->getData('new-name'), '.' . $extension) === false)
						 $upload->move($file, $path . DIRECTORY_SEPARATOR . $folder . DIRECTORY_SEPARATOR, $form->getData('new-name') . '.' . $extension);
					else
						$upload->move($file, $path . DIRECTORY_SEPARATOR . $folder . DIRECTORY_SEPARATOR, $form->getData('new-name'));
				}
				else{
					$form->error('file', Lang::get(self::PLUGIN_NAME . '.no-file-error'));
          			return $form->response(Form::STATUS_CHECK_ERROR, Lang::get(self::PLUGIN_NAME . '.no-file-error'));
				}
			}

			return $form->response(Form::STATUS_SUCCESS);
		}
  	}

	/*
	* Edit file
	*/
	public function editFile(){
		$path = App::request()->getParams('path');
		$file = App::request()->getParams('file');
		
		$param = array(
			'id' => 'fileManager-editFile',
			'fieldsets' => array(
				'general' => array(
					
					new RadioInput(array(
						'name' => 'typeFileAction',
						'label' => Lang::get(self::PLUGIN_NAME . '.add-type-action'),
						'default' => 0,
						'options' => array(
							'removeFile' => Lang::get('fileManager.removeFile-label'),
							'editNameFile' => Lang::get('fileManager.editNameFile-label'),
						),
						'layout' => 'vertical',
						'attributes' => array(
							'ko-checked' => 'editFile.type'
						)
					)),

					new HtmlInput(array(
						'name' => 'alert-remove',
						'value' => '<div class="alert alert-info"><br>' . Lang::get(self::PLUGIN_NAME . '.alert-remove-file-text') . '</br></div>'
					)),
					
					new TextInput(array(
						'name' => 'new-name',
						'label' => Lang::get(self::PLUGIN_NAME . '.new-name-label'),
					)),	
				),
				
				'_submits' => array(
					new SubmitInput(array(
						'name' => 'valid',
						'icon' => 'edit',
						'value' => Lang::get(self::PLUGIN_NAME . '.edit-submit-value'),						
					)),
				),
			),
			'onsuccess' => 'app.dialog("close");app.load(app.getUri("fileManager-index"));'
		); 

		// Get form add new plugin
		$form = new Form($param);
		
		if(!$form->submitted()){
			$this->addCss(Plugin::current()->getCssUrl('fileManager.less'));
    		$this->addJavaScript(Plugin::current()->getJsUrl('formFileEdit.js'));
			
			return View::make(Plugin::current()->getView('editFile.tpl'), array(
				'title' => Lang::get(self::PLUGIN_NAME . '.editFile-title'),
				'icon' => 'edit',
      			'form' => $form
			)); 
		}
		elseif($form->check()){
			
			if($form->getData('typeFileAction') == "removeFile"){
				shell_exec('rm ' . $path . DIRECTORY_SEPARATOR . $file);
			}
			else if($form->getData('typeFileAction') == "editNameFile"){

				$path_parts = pathinfo($file);
				if(strstr($form->getData('new-name'), '.' . $path_parts['extension']) === false)
					rename($path . DIRECTORY_SEPARATOR . $file, $path . DIRECTORY_SEPARATOR . $form->getData('new-name') . '.' . $path_parts['extension']);
				else
					rename($path . DIRECTORY_SEPARATOR . $file, $path . DIRECTORY_SEPARATOR . $form->getData('new-name'));
			}
			
			return $form->response(Form::STATUS_SUCCESS);
		}
  }
}
