<?php
class Autoloader
{
    /*
     * Ways to automatically connect classes
     */
    static private $autoloadPaths = array(
        'phpclient/*',
    );

    /*
     * Automatic connection classes
     * @return boolean - The result of the connection
     */
    public static function register()
    {
        return spl_autoload_register(array(__CLASS__, 'autoload'));
    }

    /*
     * Search class folders, with a successful search for a connection
     * @param $className string - The class name
     * @return boolean - The result of the connection
     */
    private static function autoload($className)
    {
        foreach (self::$autoloadPaths as $path) {
            $path = dirname(__FILE__) . "/" . str_replace('*', $className, $path);
            if (file_exists($path . '.php')) {
                require_once($path . '.php');
                return class_exists($className, false) || interface_exists($className, false);
            }
        }

        return false;
    }
}
