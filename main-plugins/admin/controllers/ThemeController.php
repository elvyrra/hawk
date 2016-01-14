<?php
namespace Hawk\Plugins\Admin;

class ThemeController extends Controller{

	/**
	 * Display the main page of themes
	 */
	public function index(){
		
		$tabs = array(
			'select' => array(
				'id' => 'admin-themes-select-tab',
				'title' => Lang::get('admin.theme-tab-select-title'),
				'content' => $this->compute('listThemes'),
			),
			'customize' => array(
				'id' => 'admin-themes-customize-tab',
				'title' => Lang::get('admin.theme-tab-basic-custom-title'),
				'content' => $this->compute('customize'),
			),			
			'css' => array(
				'id' => 'admin-themes-css-tab',
				'title' => Lang::get('admin.theme-tab-advanced-custom-title'),
				'content' => $this->compute('css'),
			),
			'medias' => array(
				'id' => 'admin-themes-medias-tab',
				'title' => Lang::get('admin.theme-tab-medias-title'),
				'content' => $this->compute('medias'),
			),
			'menu' => array(
				'id' => 'admin-themes-menu-tab',
				'title' => Lang::get('admin.theme-tab-menu-title'),
				'content' => MenuController::getInstance()->compute('index')
			)
		);

		$this->addJavaScript(Plugin::current()->getJsUrl('themes.js'));
		$this->addCss(Plugin::current()->getCssUrl('themes.less'));

		Lang::addKeysToJavaScript("admin.theme-delete-confirm");
		return View::make(Plugin::current()->getView("themes.tpl"), array(
			'tabs' => $tabs
		));
	}


	

	/**
	 * Display the list of available themes to choose one
	 */
	public function listThemes(){
		$themes = Theme::getAll();
		$selectedTheme = Theme::getSelected();	

		Lang::addKeysToJavaScript("admin.theme-update-reload-page-confirm");

		return View::make(Plugin::current()->getView("themes-list.tpl"), array(
			'themes' => Theme::getAll(),
			'selectedTheme' => Theme::getSelected(),
		));
	}

	

	/**
	 * Select a theme to be active
	 */
	public function select(){
		Theme::setSelected($this->name);
	}


	

	/**
	 * Customize the current selected theme
	 */
	public function customize(){
		$theme = Theme::getSelected();
		$variables = $theme->getEditableVariables();
		
		$options = $theme->getVariablesCustomValues();
		
		$param = array(
			'id' => 'custom-theme-form',
			'upload' => true,
			'action' => App::router()->getUri('customize-theme'),
			'fieldsets' => array(
				'form' => array(),

				'_submits' => array(
					new SubmitInput(array(
						'name' => 'valid',
						'value' => Lang::get('main.valid-button'),
					)),

					new ButtonInput(array(
						'name' => 'reset',
						'value' => Lang::get('admin.theme-custom-reset'),
						'class' => 'btn-default',
						'attributes' => array(
							'ko-click' => 'reset',
						)
					))
				)
			),

			'onsuccess' => '$("#theme-base-stylesheet").attr("href", data.href)',
		);

		
		foreach($variables as $var){
			switch($var['type']){
				case 'color' :
					$input = new ColorInput(array(
						'name' => $var['name'],
						'label' => Lang::get('admin.' . $var['description']),
						'value' => !empty($options[$var['name']]) ? $options[$var['name']] : $var['default']
					));
				break;

				case 'file' :
					$input = new FileInput(array(
						'name' => $var['name'],
						'label' => $var['description'],						
					));
				break;

				default :
					$input = new TextInput(array(
						'name' => $var['name'],
						'label' => $var['description'],
						'value' => !empty($options[$var['name']]) ? $options[$var['name']] : $var['default']
					));
				break;
			}
			$input->attributes = array(
				'ko-value' => 'vars["' . $input->name . '"]',
			);
			$input->labelWidth = '25em';
			$param['fieldsets']['form'][] = $input;
		}

		$form = new Form($param);
		$submitted = $form->submitted();
		if(!$submitted){
			return $form;
		}	
		else{	
			try{
				$options = array();
				foreach($variables as $var){										
					if($var['type'] == 'file'){						
						$upload = Upload::getInstance($var['name']);
						if($upload){
							$dir = $theme->getStaticDir() . 'medias/';
							if(!is_dir( $dir)){
								mkdir( $dir, 0755);
							}

							$file = $upload->getFile();
							$upload->move($file, $dir);
							
							$options[$var['name']] = $theme->getMediasUrl($filename);							
						}
					}
					else{
						$options[$var['name']] = $form->getData($var['name']);						
					}
				}

				$theme->setVariablesCustomValues($options);
				touch($theme->getStaticLessFile());

				return $form->response(Form::STATUS_SUCCESS);
			}
			catch(Exception $e){
				return $form->response(Form::STATUS_ERROR, DEBUG_MODE ? $e->getMessage() : '');
			}
		}
	}


	/**
	 * Customize the css of the current selected theme
	 */
	public function css(){
		$file = Theme::getSelected()->getCustomCssFile();
		$css = is_file($file) ? file_get_contents($file) : '';
		$param = array(
			'id' => 'theme-css-form',
			'action' => App::router()->getUri('theme-css'),
			'fieldsets' => array(
				'_submits' => array(			
					new HtmlInput(array(
						'name' => 'desctiption',
						'value' => Lang::get('admin.theme-css-description'),
					)),

					new SubmitInput(array(
						'class' => 'pull-right',
						'name' => 'valid',
						'value' => Lang::get('main.valid-button'),					
					))
				),

				'form' => array(
					new TextareaInput(array(
						'name' => 'css',
						'hidden' => true,
						'value' => $css,
						'attributes' => array(
							'ko-value' => 'css'							
						)
					)),

					new HtmlInput(array(	
						'name' => 'ace',					
						'value' => '<style id="editing-css-computed" ko-text="css">' . $css . '</style>
									<div id="theme-css-edit" contenteditable ko-ace="{language : \'css\', change : function(value){ css(value); }}">' . $css . '</div>'
					)),
				)
			)
		);

		$form = new Form($param);

		if(!$form->submitted()){
			return $form;
		}
		else{
			file_put_contents($file, $form->getData('css'));

			$form->addReturn('href', Theme::getSelected()->getCustomCssUrl() . '?' . time());

			return $form->response(Form::STATUS_SUCCESS);
		}
	}


	/**
	 * Media gallery
	 */
	public function medias(){
		$theme = Theme::getSelected();

		$rootDir = $theme->getMediasDir();

		$files = glob($rootDir . '*');		
		$medias = array(
			'image' => array(
				'icon' => 'picture-o',
				'files' => array()
			),
			'audio' => array(
				'icon' => 'music',
				'files' => array(),
			),			
			'other' => array(
				'icon' => 'file',
				'files' => array()
			),
		);
			
		$finfo = finfo_open(FILEINFO_MIME_TYPE);
		foreach($files as $file){
			if(is_file($file)){
				$mime = finfo_file($finfo, $file);
				list($category, $precision) = explode('/', $mime);
				if(!in_array($category, array('audio', 'image') )){
					$category = 'other';
				}
				
				$url = $theme->getMediasUrl(basename($file));
				switch($category){
					case 'image' :
						$medias[$category]['files'][] = array(
							'url' => $url,
							'display' => "<img src='$url' class='media-image-preview' />"
						);
						break;

					default :
						$medias[$category]['files'][] = array(
							'url' => $url,
							'display' => "<i class='icon icon-{$medias[$category]['icon']}'></i>" . basename($file)
						);
						break;
				}				
			}
		}

		Lang::addKeysToJavaScript('admin.theme-delete-media-confirm');
		return View::make(Plugin::current()->getView("theme-medias.tpl"), array(
			'medias' => $medias,				
		));
	}


	public function addMediaForm(){
		$param = array(
			'id' => 'add-theme-media-form',
			'upload' => true,
			'action' => App::router()->getUri('add-theme-media'),
			'fieldsets' => array(
				'form' => array(
					new FileInput(array(
						'name' => 'medias[]',
						'multiple' => true,
						'required' => true,						
						'nl' => false,
					)),

					new SubmitInput(array(
						'name' => 'valid',
						'icon' => 'upload',
						'value' => Lang::get('admin.theme-add-media-submit'),
					)),
				)
			),
			'onsuccess' => 'app.load(app.getUri("theme-medias"), {selector : "#admin-themes-medias-tab"});'

		);

		return new Form($param);
	}

	/**
	 * Add a new media
	 */
	public function addMedia(){
		$form = $this->addMediaForm();

		if($form->check()){
			$uploader = Upload::getInstance('medias');

			$dir = Theme::getSelected()->getMediasDir();
			if(!is_dir($dir)){
				mkdir($dir, 0755, true);
			}
			foreach($uploader->getFiles() as $file){
				$uploader->move($file, Theme::getSelected()->getMediasDir());
			}

			return $form->response(Form::STATUS_SUCCESS);
		}
	}


	/**
	 * Delete a media of the current theme
	 */
	public function deleteMedia(){
		$filename = urldecode($this->filename);
		App::fs()->remove(Theme::getSelected()->getMediasDir() . $filename);
	}



	/**
	 * The form to import a new theme
	 */
	public function importThemeForm(){
		$param = array(
			'id' => 'import-theme-form',
			'upload' => true,
			'action' => App::router()->getUri('import-theme'),
			'fieldsets' => array(
				'form' => array(
					new FileInput(array(
						'name' => 'theme',
						'required' => true,
						'extensions' => array('zip'),
						'nl' => false,
					)),

					new SubmitInput(array(
						'name' => 'valid',
						'icon' => 'upload',
						'value' => Lang::get('admin.theme-import-submit-value'),						
					)),
				)
			),
			'onsuccess' => 'app.load(app.getUri("available-themes"), { selector : $("#admin-themes-select-tab")} );'

		);

		return new Form($param);
	}


	/**
	 * Import a new theme
	 */
	public function import(){
		$form = $this->importThemeForm();
		if($form->check()){
			$uploader = Upload::getInstance('theme');

			if($uploader){
				$zip = new \ZipArchive;
				$file = $uploader->getFile();
				$zip->open($file->tmpFile);
				$zip->extractTo(THEMES_DIR);
				$zip->close();
			}

			return $form->response(Form::STATUS_SUCCESS);
		}
	}


	/**
	 * Create a custom theme
	 */
	public function create(){
		$form = new Form(array(
			'id' => 'create-theme-form',
			'labelWidth' => '20em',
			'fieldsets' => array(
				'form' => array(
					new TextInput(array(
						'name' => 'name',
						'required' => true,
						'pattern' => '/^[\w\-]+$/',
						'label' => Lang::get('admin.theme-create-name-label')
					)),

					new TextInput(array(
						'name' => 'title',
						'required' => true,
						'label' => Lang::get('admin.theme-create-title-label')
					)),

					new SelectInput(array(
						'name' => 'extends',
						'invitation' => '-',
						'options' => array_map(function($theme){
							return $theme->getTitle();
						}, Theme::getAll()),
						'label' => Lang::get('admin.theme-create-extends-label')
					)),

					new TextInput(array(
                        'name' => 'version',
                        'required' => true,
                        'pattern' => '/^(\d+\.){2,3}\d+$/',
                        'label' => Lang::get('admin.theme-create-version-label'),
                        'default' => '0.0.1'
                    )),

                    new TextInput(array(
                        'name' => 'author',
                        'label' => Lang::get('admin.theme-create-author-label'),                    
                    )),
				),
				
				'submits' => array(
					new SubmitInput(array(
						'name' => 'valid',
						'value' => Lang::get('main.valid-button')
					)),

					new ButtonInput(array(
                        'name' => 'cancel',
                        'value' => Lang::get('main.cancel-button'),
                        'onclick' => 'app.dialog("close")'
                    ))
				)
			),
			'onsuccess' => 'app.dialog("close"); app.load(app.getUri("available-themes"), { selector : $("#admin-themes-select-tab")} );'
		));

		if(!$form->submitted()){
            // Display the form
            return View::make(Theme::getSelected()->getView('dialogbox.tpl'), array(
                'title' => Lang::get('admin.theme-create-title'),
                'icon' => 'picture-o',
                'page' => $form
            ));
        }
        else{ 
        	if($form->check()){
        		$dir = THEMES_DIR . $form->getData('name') . '/';
        		if(is_dir($dir)){
        			$form->error('name', Lang::get('admin.theme-create-name-already-exists-error'));
        			return $form->response(Form::STATUS_CHECK_ERROR, Lang::get('admin.theme-create-name-already-exists-error'));
        		}

        		// The theme can be created
        		try{
        			// Create the main directory
        			if(!mkdir($dir)){
        				throw new \Exception('Impossible to create the directory ' . $dir);
        			}

        			// Create the directory views
        			if(!mkdir($dir . 'views' )){
        				throw new \Exception('Impossible to create the directory ' . $dir . 'views');
        			}

        			// Get the parent theme
        			$parent = null;
        			if($form->getData('extends')){
        				$parent = Theme::get($form->getData('extends'));
        			}

        			// Create the file manifest.json
        			$conf = array(
        				'title' => $form->getData('title'),        				
        				'version' => $form->getData('version'),
        				'author' => $form->getData('author')
        			);
        			if($parent){
        				$conf['extends'] = $parent->getName();
        			}
        			if(file_put_contents($dir . Theme::MANIFEST_BASENAME, json_encode($conf, JSON_PRETTY_PRINT)) === false){
        				throw new \Exception('Impossible to create the file ' . $dir . Theme::MANIFEST_BASENAME);
        			}

        			$theme = Theme::get($form->getData('name'));
        			if($parent){
        				// The theme extends another one, make a copy of the parent theme except manifest.json and views
        				foreach(glob($parent->getRootDir() . '*') as $element) {
        					if(! in_array(basename($element), array(Theme::MANIFEST_BASENAME, 'views'))){
        						App::fs()->copy($element, $theme->getRootDir());
        					}
        				}
        			}
        			else{
        				// Create the directory less
	        			if(!mkdir($dir . 'less' )){
	        				throw new \Exception('Impossible to create the directory ' . $dir . 'less');
	        			}

	        			// Create the file theme.less
	        			if(!touch($theme->getBaseLessFile())){
	        				throw new \Exception('Impossible to create the file ' . $theme->getBaseLessFile());
	        			}
        			}
					
        			return $form->response(Form::STATUS_SUCCESS, Lang::get('admin.theme-create-success'));
        		}
        		catch(\Exception $e){
        			if(is_dir($dir)){
        				App::fs()->remove($dir);
        			}
        			return $form->response(Form::STATUS_ERROR, DEBUG_MODE ? $e->getMessage() : Lang::get('admin.theme-create-error'));
        		}
        	}
        }
	}



	/**
	 * Delete a theme 
	 */
	public function delete(){
		$theme = Theme::get($this->name);
		if($theme->isRemovable()){
			$dir = $theme->getRootDir();
			App::fs()->remove($dir);
		}
	}


	/**
	 * Search themes on the remote platform
	 */
	public function search(){
		$api = new HawkApi;

        $search = App::request()->getParams('search');
        
        // Search themes on the API
        try{
            $themes = $api->searchThemes($search);
        }
        catch(\Hawk\HawkApiException $e){
            $themes = array();
        }

        // Remove the plugins already downloaded on the application
        foreach($themes as &$theme){
            $installed = Theme::get($theme['name']);
            $theme['installed'] = $installed !== null;
            if($installed){
                $theme['currentVersion'] = $installed->getDefinition('version');
            }
        }

        $list = new ItemList(array(
            'id' => 'search-themes-list',
            'data' => $themes,       
            'resultTpl' => Plugin::current()->getView('theme-search-list.tpl'),     
            'fields' => array()
        ));

        if($list->isRefreshing()){
            return $list->display();
        }
        else{
        	$this->addCss(Plugin::current()->getCssUrl('themes.less'));
        	$this->addJavaScript(Plugin::current()->getJsUrl('themes.js'));

            return LeftSidebarTab::make(array(
            	'page' => array(
            		'content' => $list->display(), 
            	),
            	'sidebar' => array(
            		'widgets' => array(
            			new SearchThemeWidget()
            		)
            	),
            	'icon' => 'picture-o',
            	'title' => Lang::get('admin.search-themes-result-title', array('search' => $search))
            ));
        }
	}


	/**
	 * Download a remote theme
	 */
	public function download(){
        App::response()->setContentType('json');
        try{
            $api = new HawkApi;
            $file = $api->downloadTheme($this->theme);

            $zip = new \ZipArchive;
            if($zip->open($file) !== true){
                throw new \Exception('Impossible to open the zip archive');
            }

            $zip->extractTo(THEMES_DIR);

            $theme = Theme::get($this->theme);            
            if(!$theme){
                throw new \Exception('An error occured while downloading the theme');
            }            

            return $theme;
            // App::response()->setBody($theme);
        }
        catch(\Exception $e){
            App::response()->setStatus(500);
            // App::response()->setBody(array(
            //     'message' => $e->getMessage()
            // ));
            return array(
                'message' => $e->getMessage()
            );
        }
    }


	/**
	 * Update a theme from the remote platform
	 */
	public function update(){
        $theme = Theme::get($this->theme);
        if($theme){
            App::fs()->remove($theme->getRootDir());
            $this->compute('download');
        }
    }
}