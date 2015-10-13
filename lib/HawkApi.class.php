<?php
/**
 * HawkApi.class.php
 */

namespace Hawk;

/**
 * This class treats the interface with the Hawk site, to get the informations from the site database via an Restful API
 */
class HawkApi{
    /**
     * The API base url
     */
    const BASE_URL = 'http://hawk-site.dev.elvyrra.fr/api';

    /**
     * The pattern for a plugin / theme version
     */
    const VERSION_PATTERN = '/^(\d+\.){2,3}\d+$/';

    const VERSION_PATTERN_URI = '(?:\d+\.){2,3}\d+';


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
            )
        ),
        
        // Install a plugin
        'api-install-plugin' => array(
            'method' => 'get',
            'uri' => '/plugins/{name}/install',
            'where' => array('name' => Plugin::NAME_PATTERN)            
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
            'uri' => '/plugins/{name}/update',
            'where' => array('name' => Plugin::NAME_PATTERN),
            'input' => array(
                'from' => array(
                    'required' => true,
                    'pattern' => self::VERSION_PATTERN
                ),

                'to' => array(
                    'required' => true,
                    'pattern' => self::VERSION_PATTERN 
                )
            )
        ),


        // Search themes
        'api-search-themes' => array(
            'method' => 'post',
            'uri' => '/themes/search',
            'input' => array(
                'search' => array(
                    'required' => true,
                ),
                'start' => array(
                    'pattern' => '\d*',
                    'default' => 0
                ),
                'limit' => array(
                    'pattern' => '\d*',
                    'default' => 10
                )
            )
        ),
        
        // Install a theme
        'api-install-theme' => array(
            'method' => 'get',
            'uri' => '/themes/{name}/install',
            'where' => array('name' => Theme::NAME_PATTERN)            
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
            'uri' => '/themes/{name}/update',
            'where' => array('name' => Theme::NAME_PATTERN),
            'input' => array(
                'from' => array(
                    'required' => true,
                    'pattern' => self::VERSION_PATTERN
                ),

                'to' => array(
                    'required' => true,
                    'pattern' => self::VERSION_PATTERN 
                )
            )
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
            'uri' => '/hawk/update/{from}/{to}',
            'where' => array('from' => self::VERSION_PATTERN_URI, 'to' => self::VERSION_PATTERN_URI)                
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
            'url' => self::BASE_URL . $uri,
            'method' => $route['method'],
            'contentType' => 'json',
            'dataType' => 'json',
            'body' => $body,
            'files' => $files
        ));

        $request->send();

        return $request;
    }


    /**
     * Get the available updates of Hawk
     * @return array The list of available version newer than the current one
     */
    public function getCoreUpdates(){
        $currentVersion = Utils::getHawkVersion();

        $request = $this->callApi('api-core-available-updates', array(), array('version' => $currentVersion));

        if($request->getStatusCode() == 200){
            $result = $request->getResponse();

            if(!empty($result)){                
                return $result;
            }
            else{
                return array();
            }
        }
        else{
            return array();
        }
    }


    /**
     * Download an update file for the core
     * @param string $version The version update to get
     * @param array $errors The returned errors
     * @return string The filename of the temporary file created by the content downloaded
     */
    public function getCoreUpdateArchive($version, &$errors){
        $currentVersion = Utils::getHawkVersion();

        $request = $this->callApi('api-core-update', array('from' => $currentVersion, 'to' => $version));
        if($request->getStatusCode() == 200){
            $result = $request->getResponse();

            $tmpName = TMP_DIR . uniqid() . '.zip' ;

            file_put_contents($tmpName, base64_decode($result));

            return $tmpName;
        }
        else{
            $errors = array('code' => $request->getStatusCode(), 'message' => $request->getResponse());
            return null;
        }
    }


    /**
     * Search plugins
     * @param string $search The search term
     * @param string $price The plugin type : 'all', 'free', or 'charged'
     * @param int $start The first element to get
     * @param int $limit The maximum number to get
     * @return array The list of found plugins
     */
    public function searchPlugins($search, $price, $start = 0, $limit = 20){
        $request = $this->callApi(
            'api-search-plugins', 
            array(), 
            array(
                'search' => $search,
                'price' => $price,                
            )
        );

        if($request->getStatusCode() == 200){
            return $request->getResponse();
        }
        else{
            return array();
        }
    }
}