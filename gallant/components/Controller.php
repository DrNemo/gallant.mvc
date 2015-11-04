<?php

namespace Gallant\Components;


use Gallant\Exceptions\CoreException;

class Controller
{
    protected $ctx = [];

    protected $request;

    final function __construct($request){
        $this->request = $request;

        $this->init();
    }

    protected function init(){

    }

    public function runAction($actionName){
        if(!is_callable([$this, $actionName])){
            throw new CoreException('Error 404');
        }

        $this->beforeAction();
        $this->$actionName();
        $this->afterAction();
    }

    protected function beforeAction(){

    }

    protected function afterAction(){

    }

    protected function exportsParam(array $structure){

    }
}