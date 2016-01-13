<?php
/**
 * HawkApi.php
 * @author Elvyrra SAS
 * @license MIT
 */

namespace Hawk;

/**
 * This class treats the interface with the Hawk site, to get the informations from the site database via an Restful API
 * @package Network
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
            'method' => 'get',
            'uri' => '/plugins/search',
            'where' => array(
               'params' => array(
                    'search' => array(
                        'required' => true,
                    )
                )
            )            
        ),
        
        // Install a plugin
        'api-install-plugin' => array(
            'method' => 'get',
            'uri' => '/plugins/{name}/install',
            'where' => array(
                'path' => array(
                    'name' => Plugin::NAME_PATTERN
                )
            ),
            'dataType' => 'application/octet-stream'
        ),

        // Get the available updates on plugins
        'api-plugins-available-updates' => array(
            'method' => 'get',
            'uri' => '/plugins/available-updates',
            'where' => array(
                'params' => array(
                    'plugins' => array(
                        'required' => true, 
                    )
                )
            )
        ),

        // Search themes
        'api-search-themes' => array(
            'method' => 'get',
            'uri' => '/themes/search',
            'where' => array(
                'params' => array(
                    'search' => array(
                        'required' => true,
                    )
                )
            )
        ),
        
        // Install a theme
        'api-install-theme' => array(
            'method' => 'get',
            'uri' => '/themes/{name}/install',
            'where' => array(
                'path' => array(
                    'name' => Theme::NAME_PATTERN
                )
            ),
            'dataType' => 'application/octet-stream'
        ),

        // Get the available updates on themes
        'api-themes-available-updates' => array(
            'method' => 'get',
            'uri' => '/themes/available-updates',
            'where' => array(
                'params' => array(
                    'themes' => array(
                        'required' => true,                    
                    )
                )
            )
        ),

        // Search for available updates on the core
        'api-core-available-updates' => array(
            'method' => 'get',
            'uri' => '/hawk/updates',
            'where' => array(
                'params' =>  array(
                    'version' => array(
                        'required' => true,
                        'pattern' => self::VERSION_PATTERN
                    )
                )
            )
        ),

        'api-core-update' => array(
            'method' => 'get',
            'uri' => '/hawk/update/{to}',
            'where' => array(
                'path' => array(
                    'to' => self::VERSION_PATTERN_URI
                )
            ),
            'dataType' => 'application/octet-stream'
        )
    );

    
    /**
     * Call the API
     * @param string $routeName The route name to call
     * @param array $param The URL parameter to set
     * @param array $data An associative array of the data to send in the request : 'params', 'body', 'files'
     */
    private function callApi($routeName, $param = array(), $data = array()){
        $route = self::$routes[$routeName];

        $data = array_merge(
            array(
                'params' => array(),
                'body' => array(),
                'files' => array()
            ),
            $data
        );

        $uri = $route['uri'];
        foreach($param as $key => $value){
            $uri = str_replace('{' . $key . '}', $value, $uri);
        }

        if(!empty($data['params'])){
            $uri .= '?' . http_build_query($data['params']);
        }

        $request = new HTTPRequest(array(
            'url' => HAWK_SITE_URL . '/api' . $uri,
            'method' => $route['method'],
            'contentType' => 'json',
            'dataType' => isset($route['dataType']) ? $route['dataType'] : 'json',
            'body' => $data['body'],
            'files' => $data['files']
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
            throw new HawkApiException((string) $request->getResponse(), (int) $request->getStatusCode());
        }
    }


    /**
     * Get the available updates of Hawk
     * @return array The list of available version newer than the current one
     */
    public function getCoreAvailableUpdates(){
        return $this->callApi(
            'api-core-available-updates', 
            array(), 
            array(
                'params' => array(
                    'version' => HAWK_VERSION
                )
            )
        );
    }


    /**
     * Download an update file for the core
     * @param string $version The version update to get
     * @return string The filename of the temporary file created by the downloaded content
     */
    public function getCoreUpdateArchive($version){
        return $this->callApi(
            'api-core-update', 
            array('to' => $version)
        );        
    }


    /**
     * Search plugins
     * @param string $search The search term
     * @return array The list of found plugins
     */
    public function searchPlugins($search){
        return $this->callApi(
            'api-search-plugins', 
            array(), 
            array(
                'params' => array(
                    'search' => $search
                )
            )
        );
    }


    /**
     * Download a plugin file
     * @param string $name The plugin name to download
     * @return string The filename of the temporary file created by the downloaded content 
     */
    public function downloadPlugin($name){
        return $this->callApi(
            'api-install-plugin', 
            array('name' => $name)
        );
    }


    /**
     * Search for updates on a list of plugins
     * @param array $plugins The list of plugins to search available updates for, where keys are plugin names, and values their current version
     */
    public function getPluginsAvailableUpdates($plugins){
        return $this->callApi(
            'api-plugins-available-updates', 
            array(), 
            array(
                'params' => array(
                    'plugins' => json_encode($plugins)
                )
            )
        );
    }


    /**
     * Search themes
     * @param string $search The search term
     * @return array The list of found themes
     */
    public function searchThemes($search){
        return $this->callApi(
            'api-search-themes', 
            array(), 
            array(
                'params' => array(
                    'search' => $search
                )
            )
        );
    }


    /**
     * Download a theme file
     * @param string $name The theme name to download
     * @return string The filename of the temporary file created by the downloaded content 
     */
    public function downloadTheme($name){
        return $this->callApi(
            'api-install-theme', 
            array(
                'name' => $name
            )
        );
    }


    /**
     * Search for updates on a list of themes
     * @param array $themes The list of themes to search available updates for, where keys are themes names, and values their current version
     */
    public function getThemesAvailableUpdates($themes){
        return $this->callApi(
            'api-themes-available-updates', 
            array(), 
            array(
                'params' => array(
                    'themes' => json_encode($themes)
                )
            )
        );
    }    
}

/**
 * This class describes Exceptions throwed by a request to the A.P.I
 * @package Exceptions
 */
class HawkApiException extends \Exception{}