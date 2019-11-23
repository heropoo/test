<?php
namespace Moon\Config;

/**
 * Class Config
 * @package Moon\Config
 * @date 2017-2-10
 */
class Config
{
    protected $configFileDir;

    public function __construct($configFileDir)
    {
        if (!is_dir($configFileDir)) {
            throw new Exception('This config file dir `'.$configFileDir.'` is invalided');
        }
        $this->configFileDir = realpath($configFileDir);
    }

    /**
     * get a config
     * @param string $key
     * @param bool $throw
     * @return mixed|null
     * @throws Exception
     */
    public function get($key, $throw = false)
    {
        $arr = explode('.', $key);
        $configFile = $this->configFileDir . DIRECTORY_SEPARATOR . $arr[0] . '.php';
        if (!file_exists($configFile)) {
            if ($throw) {
                throw new Exception("Config file `$configFile` is not exists");
            } else {
                return null;
            }
        }
        unset ($arr[0]);
        $config = require $configFile;

        $count = count($arr);
        if ($count == 0) {
            return $config;
        } else {
            $arr = array_values($arr);
            $path = '';
            $value = $config;
            for ($i = 0; $i < $count; $i++) {
                $key = $arr[$i];
                $path .= '[' . $key . ']';
                if (!isset($value[$key])) {
                    if ($throw) {
                        throw new Exception("Offset `Array{$path}` is not defined in config file `$configFile`");
                    } else {
                        return null;
                    }
                }
                $value = $value[$key];
            }
        }
        return $value;
    }
}

