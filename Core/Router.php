<?php

namespace Core;

/**
 * Base Router
 *
 * @author Abdul Awal <abdulawal.me>
 */
class Router
{
    /**
     * Associative array of routes (the routing table)
     * @var array
     */
    protected $routes = [];

    /**
     * Parameters from the matched route
     * @var array
     */
    protected $params = [];

    /**
     * Add a get route to the routing table
     *
     * @param string $route  The route URL
     * @param array  $params Parameters (controller, action, etc.)
     *
     * @return void
     */
    public function get($route, $params = [])
    {
        $this->params = $params;
        $this->routes[$route] = $params;

        return $this->dispatch($route, $params, 'GET');
    }

    /**
     * Add a post route to the routing table
     *
     * @param string $route  The route URL
     * @param array  $params Parameters (controller, action, etc.)
     *
     * @return void
     */
    public function post($route, $params = [])
    {
        $this->params = $params;
        $this->routes[$route] = $params;

        return $this->dispatch($route, $params, 'POST');
    }

    /**
     * Add a put route to the routing table
     *
     * @param string $route  The route URL
     * @param array  $params Parameters (controller, action, etc.)
     *
     * @return void
     */
    public function put($route, $params = [])
    {
        $this->params = $params;
        $this->routes[$route] = $params;

        return $this->dispatch($route, $params, 'PUT');
    }

    /**
     * Add a delete route to the routing table
     *
     * @param string $route  The route URL
     * @param array  $params Parameters (controller, action, etc.)
     *
     * @return void
     */
    public function delete($route, $params = [])
    {
        $this->params = $params;
        $this->routes[$route] = $params;

        return $this->dispatch($route, $params, 'DELETE');
    }

    /**
     * Add a route to the routing table
     *
     * @param string $route  The route URL
     * @param array  $params Parameters (controller, action, etc.)
     *
     * @return void
     */
    public function add($route, $params)
    {
        // Convert the route to a regular expression: escape forward slashes
        //$route = preg_replace('/\//', '\\/', $route);

        // Convert variables e.g. {controller}
        //$route = preg_replace('/\{([a-z]+)\}/', '(?P<\1>[a-z-]+)', $route);

        // Convert variables with custom regular expressions e.g. {id:\d+}
        //$route = preg_replace('/\{([a-z]+):([^\}]+)\}/', '(?P<\1>\2)', $route);

        // Add start and end delimiters, and case insensitive flag
        //$route = '/^' . $route . '$/i';
        
        return $this->dispatch($route, $params);
    }

    /**
     * Get all the routes from the routing table
     *
     * @return array
     */
    public function getRoutes()
    {
        return $this->routes;
    }

    /**
     * Match the route to the routes in the routing table, setting the $params
     * property if a route is found.
     *
     * @param string $url The route URL
     *
     * @return boolean  true if a match found, false otherwise
     */
    public function match($url)
    {
        foreach ($this->routes as $route => $params) {
            if (preg_match($route, $url, $matches)) {
                // Get named capture group values
                foreach ($matches as $key => $match) {
                    if (is_string($key)) {
                        $params[$key] = $match;
                    }
                }

                $this->params = $params;
                return true;
            }
        }

        return false;
    }

    /**
     * Get the currently matched parameters
     *
     * @return array
     */
    public function getParams()
    {
        return $this->params;
    }

    /**
     * Dispatch the route, creating the controller object and running the
     * action method
     *
     * @param string $route
     * @param array $params
     * @param string $type
     * @return void
     */
    public function dispatch($route, $params, $type)
    {
        $server_data = $_SERVER;
        $root = str_replace('index.php', '', $_SERVER['SCRIPT_NAME']);
        $uri = $server_data['REQUEST_SCHEME'].'://'.$server_data['SERVER_NAME'].':'.$server_data['SERVER_PORT'].$root;
        $redirect_uri = isset($server_data['REDIRECT_URL']) ? $server_data['REDIRECT_URL'] : '';
        $match_uri = str_replace($uri, '', $redirect_uri);

        $routh_param = [$route, $params];
        if (in_array($match_uri, $routh_param)) {
            if ($server_data['REQUEST_METHOD'] == $type) {
                $controller = $this->params['controller'];
                $controller = $this->convertToStudlyCaps($controller);
                $controller_namespace = $this->getNamespace() . $controller; 

                if (file_exists(BASEPATH.$controller_namespace.'.php')) {
                    if (class_exists($controller)) {
                        $controller_object = new $controller($this->params);

                        $action = $this->params['action'];
                        $action = $this->convertToCamelCase($action);

                        if (method_exists($controller_object, $action)) {
                            $controller_object->$action();

                        } else {
                            throw new \Exception("Method $action (in controller $controller_namespace) not found");
                        }
                    } else {
                        throw new \Exception("Controller class $controller_namespace not found");
                    }
                } else {
                    throw new \Exception("Controller file ".$controller_namespace.".php not found");
                }
            } else {
                throw new \Exception("Method not allowed");
            }
        } else {
            return false;
            //echo "not match <br>";
        }

        /*$url = $_SERVER['QUERY_STRING'];
        $url = $this->removeQueryStringVariables($url);

        if ($this->match($url)) {
            
        } else {
            throw new \Exception('No route matched.', 404);
        }*/
    }

    /**
     * Convert the string with hyphens to StudlyCaps,
     * e.g. post-authors => PostAuthors
     *
     * @param string $string The string to convert
     *
     * @return string
     */
    protected function convertToStudlyCaps($string)
    {
        return str_replace(' ', '', ucwords(str_replace('-', ' ', $string)));
    }

    /**
     * Convert the string with hyphens to camelCase,
     * e.g. add-new => addNew
     *
     * @param string $string The string to convert
     *
     * @return string
     */
    protected function convertToCamelCase($string)
    {
        return lcfirst($this->convertToStudlyCaps($string));
    }

    /**
     * Remove the query string variables from the URL (if any). As the full
     * query string is used for the route, any variables at the end will need
     * to be removed before the route is matched to the routing table. For
     * example:
     *
     *   URL                           $_SERVER['QUERY_STRING']  Route
     *   -------------------------------------------------------------------
     *   localhost                     ''                        ''
     *   localhost/?                   ''                        ''
     *   localhost/?page=1             page=1                    ''
     *   localhost/posts?page=1        posts&page=1              posts
     *   localhost/posts/index         posts/index               posts/index
     *   localhost/posts/index?page=1  posts/index&page=1        posts/index
     *
     * A URL of the format localhost/?page (one variable name, no value) won't
     * work however. (NB. The .htaccess file converts the first ? to a & when
     * it's passed through to the $_SERVER variable).
     *
     * @param string $url The full URL
     *
     * @return string The URL with the query string variables removed
     */
    protected function removeQueryStringVariables($url)
    {
        if ($url != '') {
            $parts = explode('&', $url, 2);

            if (strpos($parts[0], '=') === false) {
                $url = $parts[0];
            } else {
                $url = '';
            }
        }

        return $url;
    }

    /**
     * Get the namespace for the controller class. The namespace defined in the
     * route parameters is added if present.
     *
     * @return string The request URL
     */
    protected function getNamespace()
    {
        $namespace = 'Controllers\\';

        if (array_key_exists('namespace', $this->params)) {
            $namespace .= $this->params['namespace'] . '\\';
        }

        return $namespace;
    }

    private $instances = [];

    public function create($value)
    {
        return $this->instances[] = new Router($value);
    }

    public function getInstances()
    {
        return $this->instances;
    }
}
