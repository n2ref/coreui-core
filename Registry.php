<?php
namespace CoreUI;
use CoreUI\Utils;

require_once 'Utils/Db/Db.php';


/**
 * Class Init
 * @package CoreUI
 */
class Registry {

    /**
     * @var Utils\Db
     */
    protected static $db;

    /**
     * @var string
     */
    protected static $lang = 'en';


    /**
     * Registry constructor.
     */
    private function __construct() {}


    /**
     * @param \PDO|\mysqli|object|resource $db
     */
    public static function setDbConnection($db) {
        self::$db = new Utils\Db($db);
    }

    /**
     * @return Utils\Db|null
     */
    public static function getDbConnection() {
        return self::$db;
    }


    /**
     * @param string $lang
     */
    public static function setLanguage($lang) {
        self::$lang = $lang;
    }


    /**
     * @return string
     */
    public static function getLanguage() {
        return self::$lang;
    }
}