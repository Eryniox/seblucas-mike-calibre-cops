<?php
/**
 * COPS (Calibre OPDS PHP Server) class file
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 * @author     mikespub
 */

namespace SebLucas\Cops;

/**
 * Minimal Framework
 */
class Framework
{
    /**
     * Summary of handlers
     * @var array<string, mixed>
     */
    protected static $handlers = [
        "index" => Handlers\IndexHandler::class,
        "feed" => Handlers\FeedHandler::class,
        "json" => Handlers\JsonHandler::class,
        "fetch" => Handlers\FetchHandler::class,
        "read" => Handlers\ReadHandler::class,
        "epubfs" => Handlers\EpubFsHandler::class,
        "restapi" => Handlers\RestApiHandler::class,
        "check" => Handlers\CheckHandler::class,
        "opds" => Handlers\OpdsHandler::class,
        "loader" => Handlers\LoaderHandler::class,
        "download" => Handlers\DownloadHandler::class,
        "calres" => Handlers\CalResHandler::class,
        "zipfs" => Handlers\ZipFsHandler::class,
        "mail" => Handlers\MailHandler::class,
    ];

    /**
     * Get request instance
     * @param string $name
     * @param bool $parse
     * @return Input\Request
     */
    public static function getRequest($name = '', $parse = true)
    {
        // fix PATH_INFO when accessed via traditional endpoint scripts
        if (!empty($name) && Input\Route::addPrefix($name)) {
            if (empty($_SERVER['PATH_INFO']) || $_SERVER['PATH_INFO'] == '/') {
                $_SERVER['PATH_INFO'] =  '/' . $name;
            } elseif (!str_starts_with($_SERVER['PATH_INFO'], '/' . $name . '/')) {
                $_SERVER['PATH_INFO'] =  '/' . $name . $_SERVER['PATH_INFO'];
                // @todo force parsing route urls here?
                Input\Config::set('use_route_urls', 1);
            }
        }
        return new Input\Request($parse);
    }

    /**
     * Get handler instance based on name
     * @param string $name
     * @param mixed $args
     * @return mixed
     */
    public static function getHandler($name, ...$args)
    {
        return new static::$handlers[$name](...$args);
    }
}
