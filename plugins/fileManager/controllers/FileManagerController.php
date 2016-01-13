<?php

namespace Hawk\Plugins\FileManager;

class FileManagerController extends Controller{

	const PLUGIN_NAME = 'fileManager';
	const ROOT_FILE_MANAGER_DIR = '/home/devs/manager/static/plugins/fileManager/userfiles/';

	/**
	 * Entry point for the page of fileManager
	 */
	public function index(){
		// Get list of all subject
		$tree = $this->getMainTree();
		$preview = $this->getPreviewFile();

		header('X-Frame-Options: GOFORIT'); 
		// Add css file
	    $this->addCss(Plugin::current()->getCssUrl('fileManager.less'));
	        
	    // Add javascript file
		$this->addJavaScript(Plugin::current()->getJsUrl('jquery.media.js'));
	    $this->addJavaScript(Plugin::current()->getJsUrl('jquery.gdocsviewer.js'));
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
	public function getPreviewFile(){
    	return View::make(Plugin::current()->getView('preview.tpl'), array(
			//'file' => Plugin::current()->getUserfilesUrl() . 'Documents/facture.pdf',
		)); 
 	}

	/*
	* Get Main tree for ROOT_FILE_MANAGER_DIRECTORY
	*/
	public function getMainTree(){
    	// Scan temporary directory and check if it's contains dir with plugin name
		$tree = $this->scanFolder('/home/devs/manager/static/plugins/fileManager/userfiles');
    	
    	return View::make(Plugin::current()->getView('tree.tpl'), array(
				'tree' => $tree
			)); 
    }

	/*
	* Scan folder and extract file and folder to build tree
	*/
	public function scanFolder($dir) {
   		$result = "<ul>";
		
   		$cdir = scandir($dir); 
		$path = str_replace("/home/devs/manager/static/plugins/fileManager/userfiles/", "", $dir);
		$path = Plugin::current()->getUserfilesUrl() . $path;
   		
   		foreach ($cdir as $key => $value){ 
	      	if (!in_array($value,array(".",".."))){ 
	         
		        if (is_dir($dir . DIRECTORY_SEPARATOR . $value)){
		        	
		        	$result = $result . "<li><span><i class='icon icon-folder-open icon-3x'></i>" .  $value . "</span>";
					$result = $result . "<i class='icon icon-edit icon-lg edit-folder' data-path='" . $dir . "' data-folder='" . $value . "' ></i>";
					// recusrive
		          	$result = $result . $this->scanFolder($dir . DIRECTORY_SEPARATOR . $value); 
		          	$result = $result . "</li>";
		        } 
		        else{ 
					$result = $result . "<li><span class='preview-file' data-path='" . $path . DIRECTORY_SEPARATOR . $value . "'><i class='icon icon-file icon-3x' ></i>" . $value . "</span>";
					$result = $result . "<i class='icon icon-edit icon-lg edit-file' data-path='" . $dir . "' data-file='" . $value . "' ></i></li>";
				} 
			} 
   		} 

   		$result = $result . "</ul>";
   		return $result; 
	} 
	
	/*
	* Add new element
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
				$uploader = Upload::getInstance('file');
				if($uploader){
					$tempFile = $uploader->getFile();
					$file = $tempFile->tmpFile;
					rename($file, $path . DIRECTORY_SEPARATOR . $folder . DIRECTORY_SEPARATOR . $form->getData('new-name'));
				}
				else{
					$form->error('file', Lang::get(self::PLUGIN_NAME . '.no-file-error'));
          		return $form->response(Form::STATUS_CHECK_ERROR, Lang::get(self::PLUGIN_NAME . '.no-file-error'));
				}
			}

			return $form->response(Form::STATUS_SUCCESS);
		}
  	}
	
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
				rename($path . DIRECTORY_SEPARATOR . $file, $path . DIRECTORY_SEPARATOR . $form->getData('new-name'));
			}
			
			return $form->response(Form::STATUS_SUCCESS);
		}
  }
}
