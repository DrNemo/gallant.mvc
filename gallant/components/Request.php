<?php


namespace Gallant\Components;

use G;

class Request
{
    protected $controller = '';

    protected $request = [];

    protected $cookie = [];

    protected $domain = '';

    protected $action = '';

    protected $args = [];

    public function __construct(){
        $this->request = ['post' => $_POST, 'get' => $_GET, 'files' => $_FILES];
        $this->domain = $_SERVER['SERVER_NAME'];
        $this->cookie = $_COOKIE;
    }

    public function setRunAction($controller, $action){
        $this->controller = $controller;
        $this->action = $action;
    }

    public function setArgs($args){
        $this->args = array_merge($this->args, $args);
    }

    public function runController(){
        if(!$this->controller || !class_exists($this->controller)){
            throw new CoreException('Not found controller: ' . $this->controller);
        }
        if(!$this->action || !is_callable([$this->controller, $this->action])){
            throw new CoreException('Not found action: ' . $this->action . ' in controller: ' . $this->controller);
        }
        $controller = new $this->controller($this);
        $controller->runAction($this->action);
        //return $controller
    }
}