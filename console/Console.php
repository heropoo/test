<?php
/**
 * Created by PhpStorm.
 * User: Heropoo
 * Date: 2019/2/6
 * Time: 20:24
 */
namespace Moon\Console;

class Console
{
    public $namespace = 'App\\Commands';
    public $commands = [];

    /**
     * Add command to commands list
     * @param string $command
     * @param string|\Closure $action
     * @param string $description
     */
    public function add($command, $action, $description = ''){
        if(!$action instanceof \Closure){
            $action = $this->namespace.'\\'.$action;
        }
        $this->commands[$command] = [
            'action'=>$action,
            'description'=>$description
        ];
    }

    /**
     * Run a command
     * @param string $command
     * @param array $params
     * @return mixed
     * @throws Exception
     */
    public function runCommand($command, $params = []){
        if(!isset($this->commands[$command])){
            throw new Exception("Command '$command' is not defined");
        }
        $action = $this->commands[$command]['action'];
        if ($action instanceof \Closure) {
            return call_user_func_array($action, $params);
        } else {
            $actionArr = explode('::', $action);
            $controllerName = $actionArr[0];
            if (!class_exists($controllerName)) {
                throw new Exception("Command class '$controllerName' is not exists!");
            }
            $controller = new $controllerName;
            $methodName = $actionArr[1];
            if (!method_exists($controller, $methodName)) {
                throw new Exception("Command class method '$controllerName::$methodName' is not defined!");
            }
            return call_user_func_array([$controller, $methodName], $params);
        }
    }
}