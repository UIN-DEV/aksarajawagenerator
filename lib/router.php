<?php
/*****************************************************************
 * Author	: Harditya Rahmat Ramadhan
 * Twitter	: @freeskys
 *
 * You may change code below but PLEASE DO NOT REMOVE THIS COMMENT.
 * Thanks for using AriesPHP
 *****************************************************************/

namespace Lib;

class Router {

    //==== Routing Variable ====
    private $controller;
    private $action;
    private $value;

    //==== Cache Setting ====
    private $cacheFile;
    private $cacheFolder = 'app/caches/';
    private $cacheTime = 18000;

    //==== Controller Setting ====
    private $controller_namespace = 'App\Controllers\\';
    private $controller_prefix = 'c';
    private $not_found_controller = 'not_found';
    private $before = 'before';
    private $after = 'after';

    //==== Other Setting ====
    private $url_request = 'request';
    private $url_request_separator = '/';
    private $routing_class_separator = '#';

    /**
     * Setting the variable to process.
     */
    public function __construct() {
        //Getting page request
        $request = isset($_GET[$this->url_request]) ? $_GET[$this->url_request] : null;

        //Processing request
        $parse = explode($this->url_request_separator, $request);
        $route = @Config::getRouter($parse[0]);

        //If exist in router configuration
        if (isset($route)) {
            //Set controller, action and value
            $route_split = explode($this->routing_class_separator, $route);
            $this->controller = $route_split[0];
            $this->action = $route_split[1];
            $this->value = !empty($parse[1]) ? filter_var($parse[1], FILTER_SANITIZE_STRIPPED) : null;
        } else {
            $split = explode('/', trim($request, '/'));
            $this->controller = !empty($split[0]) ? filter_var(strtolower($split[0]), FILTER_SANITIZE_STRING) : Config::getRouter('index');
            $this->action = !empty($split[1]) ? filter_var(strtolower($split[1]), FILTER_SANITIZE_STRING) : 'index';
            $this->value = !empty($split[2]) ? filter_var($split[2], FILTER_SANITIZE_STRIPPED) : null;
        }
    }

    /**
     * Call the requested controller
     */
    public function route() {
        //Call before function
        if (is_callable($this->controller_namespace.$this->controller_prefix.'_'.$this->controller.'::'.$this->before)) {
            call_user_func($this->controller_namespace.$this->controller_prefix.'_'.$this->controller.'::'.$this->before);
        }

        //Call main function
        if (is_callable($this->controller_namespace.$this->controller_prefix.'_'.$this->controller.'::'.$this->action) &&
            $this->action != 'before' &&
            $this->action != 'after') {
            echo call_user_func($this->controller_namespace.$this->controller_prefix.'_'.$this->controller.'::'.$this->action, $this->value);
        } else {
            echo call_user_func($this->controller_namespace.$this->controller_prefix.'_'.$this->not_found_controller.'::index');
        }

        //Call after function
        if (is_callable($this->controller_namespace.$this->controller_prefix.'_'.$this->controller.'::'.$this->after)) {
            call_user_func($this->controller_namespace.$this->controller_prefix.'_'.$this->controller.'::'.$this->after);
        }
    }

    /**
     * Make cache
     */
    public function headerCache() {
        $file = $this->controller.'-'.$this->action;
        $this->cacheFile = $this->cacheFolder.'caches-'.$file.'.html';

        //Muat caches jika umurnya lebih muda dari $cacheTime
        if (file_exists($this->cacheFile) && time() - $this->cacheTime < filemtime($this->cacheFile)) {
            echo "<!-- Cached copy, generated ".date('H:i', filemtime($this->cacheFile))." -->\n";
            include($this->cacheFile);
            exit;
        }
        ob_start();
    }

    /**
     * Make cache
     */
    public function footerCache() {
        $cached = fopen($this->cacheFile, 'w');
        fwrite($cached, ob_get_contents());
        fclose($cached);
        ob_end_flush();
    }

}

?>