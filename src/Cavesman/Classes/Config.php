<?php

namespace Cavesman;

class Config
{
    
    /**
     * @param string $config
     * @param $default
     * @return array|mixed|null
     */
    public static function get(string $config = '', $default = NULL)
    {
        $params = explode(".", $config);
        $file = _APP_ . "/config/" . $params[0] . ".json";
        $config = [];
        if (!is_dir(dirname($file)))
            mkdir(dirname($file), 0777, true);

        if (file_exists($file)) {
            $config = json_decode(file_get_contents($file), true);
        }

        // Env config
        $file = _APP_ . "/config/" . $params[0] . "." . self::getEnv() . ".json";

        if (file_exists($file)) {
            $config = array_replace_recursive($config, json_decode(file_get_contents($file), true));
        }

        $array = $config;
        if ($config) {

            foreach ($params as $key => $param) {

                if ($key) {
                    if (isset($array[$param])) {
                        $array = $array[$param];
                    } else {
                        $default_array = self::getDefaultArray($params, $default);
                        $config = array_replace_recursive($config, $default_array);
                        $fp = fopen($file = _APP_ . "/config/" . $params[0] . ".json", "w+");
                        fwrite($fp, json_encode($config, JSON_PRETTY_PRINT));
                        fclose($fp);
                        return $default;
                    }
                }
            }
        } else {
            $default_array = self::getDefaultArray($params, $default);
            $config = array_replace_recursive($config, $default_array);
            $fp = fopen($file = _APP_ . "/config/" . $params[0] . ".json", "w+");
            fwrite($fp, json_encode($config, JSON_PRETTY_PRINT));
            fclose($fp);
            return $default;
        }

        return $array;
    }

    public static function getEnv()
    {
        $file = _APP_ . "/config/main.json";
        if (file_exists($file)) {
            $main = json_decode(file_get_contents($file), true);
        }
        if (isset($main['env']))
            return $main['env'];
        return 'dev';
    }

    private static function getDefaultArray($params = [], $value = null)
    {
        $str = '';
        foreach ($params as $key => $param) {
            if ($key)
                $str .= '[' . $param . ']';
        }
        $arr = [];

        // Note: a different approach would be using explode() instead
        preg_match_all('/\[([^\]]*)\]/', $str, $has_keys, PREG_PATTERN_ORDER);

        if (isset($has_keys[1])) {

            $keys = $has_keys[1];
            $k = count($keys);
            if ($k > 1) {
                for ($i = 0; $i < $k - 1; $i++) {
                    $arr[$keys[$i]] = self::walk_keys($keys, $i + 1, $value);
                }
            } else {
                $arr[$keys[0]] = $value;
            }

            $arr = array_slice($arr, 0, 1);
        }

        return $arr;
    }

    private static function walk_keys($keys, $i, $value)
    {
        $a = [];
        if (isset($keys[$i + 1])) {
            $a[$keys[$i]] = self::walk_keys($keys, $i + 1, $value);
        } else {
            $a[$keys[$i]] = $value;
        }
        return $a;
    }
}
