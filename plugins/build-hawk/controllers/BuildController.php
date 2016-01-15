<?php

namespace Hawk\Plugins\BuildHawk;

class BuildController extends Controller{
	const PLUGIN_NAME = Installer::PLUGIN_NAME;

	/**
	 *  Display the list of built and deployed versions
	 */
	public function index(){
		return NoSidebarTab::make(array(
			'page' => $this->compute('buildsList'),
			'icon' => 'cubes',
			'title' => Lang::get(self::PLUGIN_NAME . '.builds-list-page-title'),
		));
	}

	public function buildsList(){
		$list = new ItemList(array(
			'id' => 'build-hawk-builds-list',
			'action' => App::router()->getUri('build-hawk-builds-list'),
			'model' => __NAMESPACE__ . '\\HawkBuild',
			'controls' => array(
				array(
					'icon' => 'plus',
					'class' => 'btn-success',
					'label' => Lang::get(self::PLUGIN_NAME . '.new-build-btn'),
					'href' => App::router()->getUri('build-hawk-new-build'),
					'target' => 'dialog'
				)
			),
			'fields' => array(
				'version' => array(
					'label' => Lang::get(self::PLUGIN_NAME . '.builds-list-version-label'),
				),

				'createTime' => array(
					'label' => Lang::get(self::PLUGIN_NAME . '.builds-list-create-time-label'),
					'display' => function($value){
						return date(Lang::get('main.time-format'), $value);
					}
				),

				'updateTime' => array(
					'label' => Lang::get(self::PLUGIN_NAME . '.builds-list-update-time-label'),
					'display' => function($value){
						return date(Lang::get('main.time-format'), $value);
					}
				),

				'status' => array(
					'label' => Lang::get(self::PLUGIN_NAME . '.builds-list-status-label'),
					'search' => array(
						'type' => 'select',
						'options' => HawkBuild::$status,
						'invitation' => '-',
						'emptyValue' => '',
					),
					'display' => function($value){
						return HawkBuild::$status[$value];
					}
				),

				'actions' => array(
					'independant' => true,
					'display' => function($value, $field, $line){
						switch((int) $line->status){
							case HawkBuild::STATUS_OPEN :
								break;

							case HawkBuild::STATUS_BUILT :
								$dev = new ButtonInput(array(
									'label' => Lang::get(self::PLUGIN_NAME . '.deploy-dev-btn'),
									'class' => 'btn-info deploy-build',
									'attributes' => array(
										'data-id' => $line->id,
										'data-env' => 'dev'
									)
								));

								$prod = new ButtonInput(array(
									'href' => App::router()->getUri('build-hawk-deploy', array('id' => $line->id, 'env' => 'prod')),
									'label' => Lang::get(self::PLUGIN_NAME . '.deploy-prod-btn'),
									'class' => 'btn-warning deploy-build',
									'attributes' => array(
										'data-id' => $line->id,
										'data-env' => 'prod'
									)
								));

								return $dev->display() . $prod->display();
								break;

							case HawkBuild::STATUS_TESTED :
								break;

							case HawkBuild::STATUS_DEPLOYED :
								return '';
								break;
						}
					},
					'search' => false
				)
			)

		));

		Lang::addKeysToJavascript('build-hawk.deploy-error', 'build-hawk.deploy-error-412', 'build-hawk.deploy-error-401', 'build-hawk.deploy-success');
		$this->addJavaScript(Plugin::current()->getJsUrl('build-hawk.js'));
		return $list->display();
	}


	/**
	 * Build a new version of Hawk
	 */
	public function build(){
		$versions = array_map(function($version){
			return $version->version;
		}, HawkBuild::getAll('version'));

		$versions = array_reverse($versions);

		$newVersion = `git archive --remote=int HEAD version.txt | tar -xO`;

		$form = new Form(array(
			'id' => 'new-build-form',
			'model' => __NAMESPACE__ . '\\HawkBuild',
			'fieldsets' => array(
				'form' => array(
					new TextInput(array(
						'name' => 'version',
						'pattern' => HawkBuild::VERSION_PATTERN,
						'label' => Lang::get(self::PLUGIN_NAME . '.new-build-new-version-label'),
						'default' => $newVersion,
						'readonly' => true,
					)),

					new SelectInput(array(
						'name' => 'fromVersion',
						'options' => $versions,
						'label' => Lang::get(self::PLUGIN_NAME . '.new-build-from-version-label')
					)),

					new CheckboxInput(array(
						'name' => 'override',
						'independant' => true,
						'label' => Lang::get(self::PLUGIN_NAME . '.new-build-override-label'),
					)),
				),

				'submits' => array(
					new SubmitInput(array(
						'name' => 'launch',
						'value' => Lang::get(self::PLUGIN_NAME . '.new-build-form-submit-btn'),
						'icon' => 'cubes',
					)),

					new ButtonInput(array(
						'name' => 'cancel',
						'value' => Lang::get('main.cancel-button'),
						'onclick' => 'app.dialog("close")'
					))
				)
			),

			'onsuccess' => 'app.dialog("close"); app.load(app.getUri("build-hawk-index"))'
		));

		if(!$form->submitted()){
			return View::make(Theme::getSelected()->getView('dialogbox.tpl'), array(
				'page' => $form,
				'title' => Lang::get(self::PLUGIN_NAME . '.new-build-title'),
				'icon' => 'cubes'
			));
		}
		else{
			if($form->check()){
				if($form->getData('fromVersion') == $form->getData('version')){
					$form->error('fromVersion', Lang::get(self::PLUGIN_NAME . '.from-version-equals-version'));
					return $form->response(Form::STATUS_CHECK_ERROR);
				}

				$lastBuild = HawkBuild::getByVersion($form->getData('fromVersion'));
				if(!$lastBuild){
					// The
					$form->error('fromVersion', Lang::get(self::PLUGIN_NAME . '.from-version-not-existing'));
					return $form->response(Form::STATUS_CHECK_ERROR);
				}

				$build = HawkBuild::getByVersion($form->getData('version'));
				if($build && !$form->getData('override')){
					// The version already exists
					$form->error('version', Lang::get(self::PLUGIN_NAME . '.version-already-exists'));
					return $form->response(Form::STATUS_CHECK_ERROR);
				}
				elseif(!$build){
					$build = new HawkBuild(array(
						'version' => $form->getData('version'),
						'fromVersion' => $form->getData('fromVersion'),
						'createTime' => time(),
						'status' => HawkBuild::STATUS_OPEN
					));
				}

				// Prepare the folder that will home the update
				$buildDir = $build->getBuildDirname();

				// Clean the folder and recreate it
				if(is_dir($buildDir)){
					shell_exec('rm -r ' . $buildDir);
				}
				mkdir($buildDir, 0775, true);

				// Create the folder to-update, containing the files that have been added or updated
		        $toUpdateDir = $buildDir . 'to-update/';
		        mkdir($toUpdateDir, 0775);

		        // Create the file to-delete.txt, containing the list of files that have been removed
		        $toDeleteFile = $buildDir . 'to-delete.txt';
		        touch($toDeleteFile);


				// Open the git repository
				$repo = \Git::open(HawkBuild::REPOSITORY_DIR);

				$tags = $repo->list_tags($build->getTag());
		        if(!empty($tags)){
		            $repo->remove_tag($build->getTag());
		        }

		        // Add the tag to the current version
		        $repo->add_tag($build->getTag());

		        // Create the directory containing the final .zip file
		        $zipFile = $build->getBuildZipFilename();
		        $zipDir = dirname($zipFile);

		        if(!is_dir($zipDir)){
		            mkdir($zipDir, 0775);
		        }

		        $prefix = 'update-' . $build->getTag() . '/';
		        // Init the zip file with the modified and added files
		        $repo->run('archive --prefix=' . $prefix . 'to-update/ -o ' . $zipFile . ' ' . $build->getTag() . ' $(git diff --name-only ' . $lastBuild->getTag() . ' ' . $build->getTag() . ')' );

	        	$zip = new \ZipArchive;
	        	$zip->open($zipFile);

		        // Add the deleted files in to-delete.txt
		        file_put_contents($toDeleteFile, $repo->run('diff --diff-filter=D --name-only ' . $lastBuild->getTag() . ' ' . $build->getTag()));

		        $zip->addFile($toDeleteFile, $prefix . 'to-delete.txt');

		        $zip->close();

		        $build->set(array(
		        	'updateTime' => time(),
		        	'status' => HawkBuild::STATUS_BUILT
		        ));

		        $build->save();

		        return $form->response(Form::STATUS_SUCCESS);
			}
		}
	}


	/**
	 * Run the tests on Hawk
	 */
	public function test(){}


	/**
	 * Deploy a version of Hawk. The deployment is in two phases :
	 * 	1. Push the code on github
	 * 	2. Deploy the update on Hawk site by the API
	 */
	public function deploy(){
		$build = HawkBuild::getById($this->id);

		if($this->env === 'prod'){
			try{
				// Development environment - Push on github
				$repo = \Git::open(HawkBuild::REPOSITORY_DIR);

				$repo->push('origin', 'master');
			}
			catch(\Exception $e){
				App::response()->setStatus(500);
				throw $e;
			}
		}

		// Send the update on the website
		$url = ($this->env === 'dev' ? HawkApiDev::BASE_URL : HAWK_SITE_URL . '/api') . '/hawk/update/deploy';
		$request = new HTTPRequest(array(
			'method' => HTTPRequest::METHOD_POST,
			'url' => $url,
			'files' => array(
				'updateFile' => $build->getBuildZipFilename()
			),
			'body' => array(
				'version' => $build->version,
				'username' => 'elvyrra',
				'password' => '4sq6d54#!-87d#s-qd'
			),
			'dataType' => 'json',
		));

		$request->send();

		if($request->getStatusCode() == 201){
			if($this->env == 'prod'){
				$build->set(array(
					'status' => HawkBuild::STATUS_DEPLOYED,
					'updateTime' => time()
				));

				$build->update();
			}
		}

		App::response()->setContentType('json');
		App::response()->setStatus($request->getStatusCode());
		App::response()->setBody($request->getResponse());
	}
}