<?php
/**
 * HawkApi.php
 * @author Elvyrra SAS
 * @license MIT
 */

namespace Hawk;

/**
 * This class treats the interface with the Hawk site, to get the informations from the site database via an Restful API
 */
class HawkApi{
    /**
     * The pattern for a plugin / theme version
     */
    const VERSION_PATTERN = '/^(\d{1,2}\.){2,3}\d{1,2}$/';

    /**
     * The pattern for a plugin or theme version, in a URI
     */
    const VERSION_PATTERN_URI = '(?:\d{1,2}\.){2,3}\d{1,2}';


    /**
     * The callable routes on the API, with their parameters
     * @var array
     */
    public static $routes = array(
        // Search plugins
        'api-search-plugins' => array(
            'method' => 'post',
            'uri' => '/plugins/search',
            'input' => array(
                'search' => array(
                    'required' => true,
                ),
                'price' => array(
                    'pattern' => '/^(all|free|charged)?$/',
                    'default' => 'all'
                )
            ),
        ),
        
        // Install a plugin
        'api-install-plugin' => array(
            'method' => 'get',
            'uri' => '/plugins/{name}/install',
            'where' => array('name' => Plugin::NAME_PATTERN),
            'dataType' => 'application/octet-stream'
        ),

        // Get the available updates on plugins
        'api-plugins-available-updates' => array(
            'method' => 'post',
            'uri' => '/plugins/available-updates',
            'input' => array(
                'plugins' => array(
                    'required' => true, 
                )
            )
        ),

        // Update a plugin
        'api-update-plugin' => array(
            'method' => 'patch',
            'uri' => '/plugins/{name}/update/{version}',
            'where' => array('name' => Plugin::NAME_PATTERN, 'version' => self::VERSION_PATTERN_URI),
            'dataType' => 'application/octet-stream'
        ),


        // Search themes
        'api-search-themes' => array(
            'method' => 'post',
            'uri' => '/themes/search',
            'input' => array(
                'search' => array(
                    'required' => true,
                ),
                'price' => array(
                    'pattern' => '/^(all|free|charged)?$/',
                    'default' => 'all'
                )
            )
        ),
        
        // Install a theme
        'api-install-theme' => array(
            'method' => 'get',
            'uri' => '/themes/{name}/install',
            'where' => array('name' => Theme::NAME_PATTERN),
            'dataType' => 'application/octet-stream'
        ),

        // Get the available updates on themes
        'api-themes-available-updates' => array(
            'method' => 'post',
            'uri' => '/themes/available-updates',
            'input' => array(
                'themes' => array(
                    'required' => true,                    
                )
            )
        ),

        // Update a theme
        'api-update-theme' => array(
            'method' => 'patch',
            'uri' => '/themes/{name}/update/{version}',
            'where' => array('name' => Theme::NAME_PATTERN, 'version' => self::VERSION_PATTERN_URI),
            'dataType' => 'application/octet-stream'         
        ),

        // Search for available updates on the core
        'api-core-available-updates' => array(
            'method' => 'post',
            'uri' => '/hawk/updates',
            'input' =>  array(
                'version' => array(
                    'required' => true,
                    'pattern' => self::VERSION_PATTERN
                )
            )
        ),

        'api-core-update' => array(
            'method' => 'get',
            'uri' => '/hawk/update/{to}',
            'where' => array('to' => self::VERSION_PATTERN_URI),
            'dataType' => 'application/octet-stream'
        )
    );

    
    /**
     * Call the API
     * @param string $routeName The route name to call
     * @param array $param The URL parameter to set
     * @param array $body The body to send
     * @param array $files The filenames to upload
     */
    private function callApi($routeName, $param = array(), $body = array(), $files = array()){
        $route = self::$routes[$routeName];

        $uri = $route['uri'];
        foreach($param as $key => $value){
            $uri = str_replace('{' . $key . '}', $value, $uri);
        }

        $request = new HTTPRequest(array(
            'url' => HAWK_SITE_URL . '/api' . $uri,
            'method' => $route['method'],
            'contentType' => 'json',
            'dataType' => isset($route['dataType']) ? $route['dataType'] : 'json',
            'body' => $body,
            'files' => $files
        ));

        $request->send();

        if($request->getStatusCode() === 200){
            $result = $request->getResponse();
            $contentType = $request->getResponseHeaders('Content-Type');
            
            if($contentType == 'application/octet-stream'){

                $tmpName = TMP_DIR . uniqid() . '.zip' ;

                file_put_contents($tmpName, base64_decode($result));

                return $tmpName;
            }
            else{
                return $result;
            }
        }
        else{
            throw new HawkApiException($request->getResponse(), $request->getStatusCode());
        }
    }


    /**
     * Get the available updates of Hawk
     * @return array The list of available version newer than the current one
     */
    public function getCoreAvailableUpdates(){
        return $this->callApi('api-core-available-updates', array(), array('version' => HAWK_VERSION));
    }


    /**
     * Download an update file for the core
     * @param string $version The version update to get
     * @return string The filename of the temporary file created by the downloaded content
     */
    public function getCoreUpdateArchive($version){
        return $this->callApi('api-core-update', array('to' => $version));        
    }


    /**
     * Search plugins
     * @param string $search The search term
     * @param string $price The plugin type : 'all', 'free', or 'charged'
     * @return array The list of found plugins
     */
    public function searchPlugins($search, $price = 'all'){
        return $this->callApi('api-search-plugins', array(), array('search' => $search, 'price' => $price) );
    }


    /**
     * Download a plugin file
     * @param string $name The plugin name to download
     * @return string The filename of the temporary file created by the downloaded content 
     */
    public function downloadPlugin($name){
        return $this->callApi('api-install-plugin', array('name' => $name));
    }


    /**
     * Search for updates on a list of plugins
     * @param array $plugins The list of plugins to search available updates for, where keys are plugin names, and values their current version
     */
    public function getPluginsAvailableUpdates($plugins){
        return $this->callApi('api-plugins-available-updates', array(), array('plugins' => $plugins));
    }


    /**
     * Download a plugin update file
     * @param string $name The plugin name
     * @param string $version The version to download
     */
    public function downloadPluginUpdate($name, $version){
        return $this->callApi('api-update-plugin', array('name' => $name, 'version' => $version));
    }


    /**
     * Search themes
     * @param string $search The search term
     * @param string $price The theme type : 'all', 'free', or 'charged'
     * @return array The list of found themes
     */
    public function searchThemes($search, $price = 'all'){
        return $this->callApi('api-search-themes', array(), array('search' => $search, 'price' => $price) );
    }


    /**
     * Download a theme file
     * @param string $name The theme name to download
     * @return string The filename of the temporary file created by the downloaded content 
     */
    public function downloadTheme($name){
        return $this->callApi('api-install-theme', array('name' => $name));
    }


    /**
     * Search for updates on a list of themes
     * @param array $themes The list of themes to search available updates for, where keys are themes names, and values their current version
     */
    public function getThemesAvailableUpdates($themes){
        return $this->callApi('api-themes-available-updates', array(), array('themes' => json_encode($themes)));
    }


    /**
     * Download a theme update file
     * @param string $name The theme name
     * @param string $version The version to download
     */
    public function downloadThemeUpdate($name, $version){
        return $this->callApi('api-update-theme', array('name' => $name, 'version' => $version));
    }
}

/**
 * This class describes Exceptions throwed by a request to the A.P.I
 */
class HawkApiException extends \Exception{}