<?php


namespace Controller\Test\Test2;

use \G as G;
use \Gallant\Prototype\controlDefault;

class ControllerTestpage extends controlDefault
{
    function actionMethod(){
        p(static::class . '->actionMethod()');
        p(G::getParam());

        G::template()->tpl();
    }
}