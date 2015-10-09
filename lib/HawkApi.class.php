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
            'where' => array('from' => self::VERSION_PATTERN, 'to' => self::VERSION_PATTERN)                
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
        $currentVersion = file_get_contents(ROOT_DIR . 'version.txt');

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
}