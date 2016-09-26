<?php
/**
 * AdminController.php
 *
 * @author  Elvyrra SAS
 * @license http://rem.mit-license.org/ MIT
 */
namespace Hawk\Plugins\Admin;

/**
 * Settings controller
 *
 * @package Plugins\Admin
 */
class AdminController extends Controller{
    const MAX_LOGO_SIZE = 200000; // 200 Ko

    /**
     * Display and treat application settings
     */
    public function settings(){
        $languages = array_map(function ($language) {
            return $language->label;
        }, Language::getAll('tag'));

        $roleObjects = Role::getListByExample(new DBExample(array(
            'id' => array('$ne' => 0)
        )), 'id');
        $roles = array();
        foreach($roleObjects as $role){
            $roles[$role->id] = Lang::get("roles.role-$role->id-label");
        }

        $items = MenuItem::getAvailableItems();

        $menuItems = array();
        foreach($items as $item){
            if($item->action && !preg_match('/^(javascript\:|#)/', $item->action) && (!$item->target || $item->target == 'newtab')) {
                if($item->label === 'user.username'){
                    $item->label = "jthaon";
                }

                $menuItems[$item->action] = $item->label;
            }
            else{
                foreach($item->visibleItems as $subitem){
                    if($item->label === 'user.username'){
                        $item->label = "jthaon";
                    }
                    
                    if(!preg_match('/^(javascript\:|#)/', $subitem->action) && (!$subitem->target || $subitem->target == 'newtab')) {
                        $menuItems[$subitem->action] = $item->label . " &gt; " . $subitem->label;
                    }
                }
            }
        }

        $api = new HawkApi;
        try{
            $updates = $api->getCoreAvailableUpdates();
        }
        catch(\Hawk\HawkApiException $e){
            $updates = array();
        }

        $param = array(
            'id' => 'settings-form',
            'upload' => true,
            'fieldsets' => array(
                'main' => array(
                    new TextInput(array(
                        'name' => 'main_sitename',
                        'required' => true,
                        'default' => Option::get('main.sitename'),
                        'label' => Lang::get('admin.settings-sitename-label')
                    )),

                    new SelectInput(array(
                        'name' => 'main_language',
                        'required' => true,
                        'options' => $languages,
                        'default' => Option::get('main.language'),
                        'label' => Lang::get('admin.settings-language-label'),
                    )),

                    new SelectInput(array(
                        'name' => 'main_timezone',
                        'required' => true,
                        'options' => array_combine(\DateTimeZone::listIdentifiers(), \DateTimeZone::listIdentifiers()),
                        'default' => Option::get('main.timezone'),
                        'label' => Lang::get('admin.settings-timezone-label')
                    )),

                    new SelectInput(array(
                        'name' => 'main_currency',
                        'required' => true,
                        'options' => array(
                            'EUR' => 'Euro (€)',
                            'USD' => 'US Dollar ($)'
                        ),
                        'default' => Option::get('main.currency'),
                        'label' => Lang::get('admin.settings-currency-label')
                    )),

                    new FileInput(array(
                        'name' => 'logo',
                        'label' => Lang::get('admin.settings-logo-label'),
                        'after' => Option::get('main.logo') ?
                            '<img src="' . Plugin::get('main')->getUserfilesUrl(Option::get('main.logo')) . '" class="settings-logo-preview" />' : '',
                        'maxSize' => 200000,
                        'extensions' => array('gif', 'png', 'jpg', 'jpeg')
                    )),

                    new FileInput(array(
                        'name' => 'favicon',
                        'label' => Lang::get('admin.settings-favicon-label'),
                        'after' => Option::get('main.favicon') ?
                            '<img src="'. Plugin::get('main')->getUserfilesUrl(Option::get('main.favicon')).'" class="settings-favicon-preview" />' : '',
                        'maxSize' => 20000,
                        'extensions' => array('gif', 'png', 'jpg', 'jpeg', 'ico')
                    ))
                ),

                'referencing' => call_user_func(function () use ($languages) {
                    $inputs = array();
                    foreach($languages as $tag => $language){
                        $inputs[] = new TextInput(array(
                            'name' => 'main_page-title-' . $tag,
                            'default' => Option::get('main.page-title-' . $tag),
                        ));

                        $inputs[] = new TextareaInput(array(
                            'name' => 'main_page-description-' . $tag,
                            'default' =>  Option::get('main.page-description-' . $tag),
                        ));

                        $inputs[] = new TextInput(array(
                            'name' => 'main_page-keywords-' . $tag,
                            'default' => Option::get('main.page-keywords-' . $tag),
                        ));
                    }
                    return $inputs;
                }),

                'home' => array(
                    new RadioInput(array(
                        'name' => 'main_home-page-type',
                        'options' => array(
                            'default' => Lang::get('admin.settings-home-page-type-default'),
                            'custom' => Lang::get('admin.settings-home-page-type-custom'),
                            'page' => Lang::get('admin.settings-home-page-type-page'),
                        ),
                        'default' => Option::get('main.home-page-type') ? Option::get('main.home-page-type') : 'default',
                        'label' => Lang::get('admin.settings-home-page-type-label'),
                        'layout' => 'vertical',
                        'attributes' => array(
                            'ko-checked' => 'homePage.type'
                        )
                    )),

                    new WysiwygInput(array(
                        'name' => 'main_home-page-html',
                        'id' => 'home-page-html',
                        'label' => Lang::get('admin.settings-home-page-html-label'),
                        'default' => Option::get('main.home-page-html'),
                    )),

                    new SelectInput(array(
                        'name' => 'main_home-page-item',
                        'id' => 'home-page-item',
                        'label' => Lang::get('admin.settings-home-page-item-label'),
                        'options' => $menuItems,
                        'value' => Option::get('main.home-page-item')
                    )),

                    new CheckboxInput(array(
                        'name' => 'main_open-last-tabs',
                        'label' => Lang::get('admin.settings-open-last-tabs'),
                        'default' => Option::get('main.open-last-tabs'),
                        'dataType' => 'int'
                    )),
                ),

                'users' => array(
                    new RadioInput(array(
                        'name' => 'main_allow-guest',
                        'options' => array(
                            0 => Lang::get('main.no-txt'),
                            1 => Lang::get('main.yes-txt'),
                        ),
                        'default' => Option::get('main.allow-guest') ? Option::get('main.allow-guest') : 0,
                        'label' => Lang::get('admin.settings-allow-guest-label')
                    )),

                    new RadioInput(array(
                        'name' => 'main_open-register',
                        'options' => array(
                            0 => Lang::get('admin.settings-open-register-off'),
                            1 => Lang::get('admin.settings-open-register-on'),
                        ),
                        'layout' => 'vertical',
                        'label' => Lang::get('admin.settings-open-registers-label'),
                        'default' => Option::get('main.open-register') ?  Option::get('main.open-register') : 0,
                        'attributes' => array(
                            'ko-checked' => 'register.open'
                        )
                    )),

                    new CheckboxInput(array(
                        'name' => 'main_confirm-register-email',
                        'label' => Lang::get('admin.settings-confirm-email-label'),
                        'default' => Option::get('main.confirm-register-email'),
                        'dataType' => 'int',
                        'attributes' => array(
                            'ko-checked' => 'register.checkEmail'
                        )
                    )),

                    new WysiwygInput(array(
                        'name' => 'main_confirm-email-content',
                        'id' => 'settings-confirm-email-content-input',
                        'default' => Option::get('main.confirm-email-content'),
                        'label' => Lang::get('admin.settings-confirm-email-content-label'),
                        'labelWidth' => 'auto',
                    )),

                    new CheckboxInput(array(
                        'name' => 'main_confirm-register-terms',
                        'label' => Lang::get('admin.settings-confirm-terms-label'),
                        'default' => Option::get('main.confirm-register-terms'),
                        'dataType' => 'int',
                        'labelWidth' => 'auto',
                        'attributes' => array(
                            'ko-checked' => 'register.checkTerms'
                        )
                    )),

                    new WysiwygInput(array(
                        'name' => 'main_terms',
                        'id' => 'settings-terms-input',
                        'label' => Lang::get('admin.settings-terms-label'),
                        'labelWidth' => 'auto',
                        'default' => Option::get('main.terms'),
                    )),

                    new SelectInput(array(
                        'name' => 'roles_default-role',
                        'label' => Lang::get('admin.settings-default-role-label'),
                        'options' => $roles,
                        'default' => Option::get('roles.default-role')
                    )),
                ),

                'email' => array(
                    new EmailInput(array(
                        'name' => 'main_mailer-from',
                        'default' => Option::get('main.mailer-from') ? Option::get('main.mailer-from') : App::session()->getUser()->email,
                        'label' => Lang::get('admin.settings-mailer-from-label')
                    )),

                    new TextInput(array(
                        'name' => 'main_mailer-from-name',
                        'default' => Option::get('main.mailer-from-name') ? Option::get('main.mailer-from-name') : App::session()->getUser()->getDisplayName(),
                        'label' => Lang::get('admin.settings-mailer-from-name-label')
                    )),

                    new SelectInput(array(
                        'name' => 'main_mailer-type',
                        'default' => Option::get('main.mailer-type'),
                        'options' => array(
                            'mail' => Lang::get('admin.settings-mailer-type-mail-value'),
                            'smtp' => Lang::get('admin.settings-mailer-type-smtp-value'),
                            'pop3' => Lang::get('admin.settings-mailer-type-pop3-value')
                        ),
                        'label' => Lang::get('admin.settings-mailer-type-label'),
                        'attributes' => array(
                            'ko-value' => 'mail.type'
                        )
                    )),

                    new TextInput(array(
                        'name' => 'main_mailer-host',
                        'default' => Option::get('main.mailer-host'),
                        'label' => Lang::get('admin.settings-mailer-host-label'),
                    )),

                    new IntegerInput(array(
                        'name' => 'main_mailer-port',
                        'default' => Option::get('main.mailer-port'),
                        'label' => Lang::get('admin.settings-mailer-port-label'),
                        'size' => 4,
                    )),

                    new TextInput(array(
                        'name' => 'main_mailer-username',
                        'default' => Option::get('main.mailer-username'),
                        'label' => Lang::get('admin.settings-mailer-username-label'),
                    )),

                    new PasswordInput(array(
                        'name' => 'main_mailer-password',
                        'encrypt' => 'Crypto::aes256Encode',
                        'decrypt' => 'Crypto::aes256Decode',
                        'default' => Option::get('main.mailer-password'),
                        'label' => Lang::get('admin.settings-mailer-password-label'),
                    )),

                    new SelectInput(array(
                        'name' => 'main_smtp-secured',
                        'options' => array(
                            '' => Lang::get('main.no-txt'),
                            'ssl' => 'SSL',
                            'tsl' => 'TSL'
                        ),
                        'label' => Lang::get('admin.settings-smtp-secured-label')
                    ))
                ),

                '_submits' => array(
                    empty($updates) ? null : new ButtonInput(array(
                        'name' => 'update-hawk',
                        'value' => Lang::get('admin.update-page-update-hawk-btn', array('version' => end($updates)['version'])),
                        'icon' => 'refresh',
                        'id' => 'update-hawk-btn',
                        'attributes' => array(
                            'ko-click' => 'function(){ updateHawk("' . end($updates)['version'] . '"); }'
                        ),
                        'class' => 'btn-warning'
                    )),

                    new SubmitInput(array(
                        'name' => 'save',
                        'value' => Lang::get('main.valid-button'),
                        'class' => 'pull-right'
                    )),
                ),
            ),
        );

        $form = new Form($param);
        if(!$form->submitted()) {
            // Display the form
            $this->addCss(Plugin::current()->getCssUrl('settings.less'));

            $page = View::make(Plugin::current()->getView('settings.tpl'), array(
                'form' => $form,
                'languages' => $languages
            ));

            Lang::addKeysToJavascript('admin.update-page-confirm-update-hawk');
            $this->addJavaScript(Plugin::current()->getJsUrl('settings.js'));

            return NoSidebarTab::make(array(
                'icon' => 'cogs',
                'title' => Lang::get('admin.settings-page-name'),
                'description' => Lang::get('admin.settings-page-description'),
                'page' => $page
            ));
        }
        else{
            // treat the form
            try{
                if($form->check()) {
                    // register scalar values
                    foreach($form->inputs as $name => $field){
                        if(!$field instanceof \Hawk\FileInput && !$field instanceof \Hawk\ButtonInput) {
                            $value = $field->dbvalue();
                            if($value === null) {
                                $value = '0';
                            }
                            $optionName = str_replace('_', '.', $name);
                            Option::set($optionName, $value);
                        }
                        elseif($field instanceof \Hawk\FileInput) {
                            $upload = Upload::getInstance($name);
                            if($upload) {
                                try{
                                    $file = $upload->getFile();


                                    $dir = Plugin::get('main')->getPublicUserfilesDir();

                                    if(!is_dir($dir)) {
                                        mkdir($dir, 0755);
                                    }

                                    if($name == 'favicon') {
                                        $basename = uniqid() . '.ico';
                                        $generator = new \PHPICO($file->tmpFile, array(
                                            array(16, 16),
                                            array(32, 32),
                                            array(48, 48),
                                            array(64, 64),
                                        ));
                                        $generator->save_ico($dir . $basename);
                                    }
                                    else{
                                        $basename = uniqid() . '.' . $file->extension;
                                        $upload->move($file, $dir, $basename);
                                    }

                                    // remove the old image
                                    @unlink($dir . Option::get("main.$name"));

                                    Option::set("main.$name", $basename);
                                }
                                catch(ImageException $e){
                                    $form->error($name, Lang::get('form.image-format'));
                                    throw $e;
                                }
                            }
                        }
                    }

                    // Register the favicon
                    App::logger()->info('The options of the application has been updated by ' . App::session()->getUser()->username);
                    return $form->response(Form::STATUS_SUCCESS, Lang::get('admin.settings-save-success'));
                }
            }
            catch(Exception $e){
                App::logger()->error('An error occured while updating application options');
                return $form->response(Form::STATUS_ERROR, DEBUG_MODE ? $e->getMessage() : Lang::get('admin.settings-save-error'));
            }
        }
    }


    /**
     * Update Hawk
     */
    public function updateHawk(){
        try{
            $api = new HawkApi;

            $nextVersions = $api->getCoreAvailableUpdates();

            if(empty($nextVersions)) {
                throw new \Exception("No newer version is available for Hawk");
            }

            // Update incrementally all newer versions
            foreach($nextVersions as $version){
                // Download the update archive
                $archive = $api->getCoreUpdateArchive($version['version']);

                // Extract the downloaded file
                $zip = new \ZipArchive;
                if($zip->open($archive) !== true) {
                    throw new \Exception('Impossible to open the zip archive');
                }
                $zip->extractTo(TMP_DIR);

                // Put all modified or added files in the right folder
                $folder = TMP_DIR . 'update-v' . $version['version'] . '/';
                App::fs()->copy($folder . 'to-update/*', ROOT_DIR);

                // Delete the files to delete
                $toDeleteFiles = explode(PHP_EOL, file_get_contents($folder . 'to-delete.txt'));

                foreach($toDeleteFiles as $file){
                    if(is_file(ROOT_DIR . $file)) {
                        unlink(ROOT_DIR . $file);
                    }
                }

                // Remove temporary files and folders
                App::fs()->remove($folder);
                App::fs()->remove($archive);
            }

            // Execute the update method if exist
            $updater = new HawkUpdater;
            $methods = get_class_methods($updater);

            foreach($nextVersions as $version){
                $method = 'v' . str_replace('.', '_', $version['version']);
                if(method_exists($updater, $method)) {
                    $updater->$method();
                }
            }

            App::cache()->clear('views');
            App::cache()->clear('lang');
            App::cache()->clear(Autoload::CACHE_FILE);
            App::cache()->clear(Lang::ORIGIN_CACHE_FILE);

            $response = array('status' => true);
        }
        catch(\Exception $e){
            $response = array('status' => false, 'message' => DEBUG_MODE ? $e->getMessage() : Lang::get('admin.update-hawk-error'));
        }

        App::response()->setContentType('json');
        return $response;
    }

}