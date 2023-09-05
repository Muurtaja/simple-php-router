<?php
include "routes.php";
foreach ($getRoutes as $route => $method) {
    Router::get($route, $method);
}
foreach ($postRoutes as $route => $method) {
    Router::post($route, $method);
}
Router::notFound();

class Router
{
    private static $nomatch = true;
    private static function getUrl()
    {
        return $_SERVER["REQUEST_URI"];
    }
    private static function getParams($pattern)
    {
        $url = self::getUrl();
        $pattern = str_replace('//', '/', $pattern);
        if (preg_match_all('~\{([^{}]+)\}~', $pattern, $matches)) {
            foreach ($matches[0] as $value) {
                $pattern = str_replace($value, "(\w+)", $pattern);
            }
        }
        $scriptName = str_replace('/index.php', '', $_SERVER["SCRIPT_NAME"]);
        $prefix = @$pattern[0] != '/' ? "$scriptName/" : $scriptName;
        $pattern = "~^{$prefix}{$pattern}(?:\?.*)?$~";
        if (preg_match($pattern, $url, $matches)) {
            return $matches;
        }
        return false;
    }
    static function get($pattern, $callback)
    {
        self::process($pattern, $callback);
    }
    static function post($pattern, $callback)
    {
        self::process($pattern, $callback, 'POST');
    }
    private static function process($pattern, $callback, $method = 'GET')
    {
        if (!self::$nomatch) return;
        $params = self::getParams($pattern);
        if ($params) {
            if ($_SERVER['REQUEST_METHOD'] != $method) {
                throw new Exception($_SERVER['REQUEST_METHOD'] . " method not allowed for this routes", 501);
            }
            self::$nomatch = false;
            $functionArguments = array_slice($params, 1);
            if (is_string($callback) && file_exists($callback)) {
                return (function ($parameters) use ($callback) {
                    $body = (object)$_POST;
                    require $callback;
                })($functionArguments);
            }
            if (is_callable($callback)) {
                if ($_SERVER['REQUEST_METHOD'] == 'POST')  array_unshift($functionArguments, (object)$_POST);
                return $callback(...$functionArguments);
            }
            throw new Exception("No action found for this route", 200);
        }
    }
    static function notFound()
    {
        if (self::$nomatch) {
            header("HTTP/1.0 404 Not Found");
        }
    }
}
