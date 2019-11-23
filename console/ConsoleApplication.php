<?php
/**
 * Created by PhpStorm.
 * User: Heropoo
 * Date: 2019/3/16
 * Time: 23:51
 */

namespace Moon\Console;


class ConsoleApplication
{
    public function handCommand(Console $console){
        $argv = $_SERVER['argv'];
        foreach ($argv as $key => $arg){
            if((strpos($arg, 'moon') + 4) == strlen($arg) || $arg === 'moon'){
                break;
            }else{
                unset($argv[$key]);
            }
        }
        if(!isset($argv[1])){
            echo 'Moon Console '.$this->version().PHP_EOL;
            echo '------------------------------------------------'.PHP_EOL;
            // command list
            ksort($console->commands);
            foreach ($console->commands as $command => $options){
                echo $command."\t\t".$options['description'].PHP_EOL;
            }
            return 0;
        }
        $command = $argv[1];
        unset($argv[0], $argv[1]);
        return $console->runCommand($command, $argv);
    }

    public function version(){
        return 'v0.1';
    }
}