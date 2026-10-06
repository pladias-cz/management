<?php declare(strict_types = 1);

namespace App\Core;

use Nette\Application\Routers\Route;
use Nette\Application\Routers\RouteList;
use Nette\StaticClass;

final class RouterFactory
{

    use StaticClass;

    public static function createRouter(): RouteList
    {
        $router = new RouteList();

        self::buildAdmin($router);
        self::buildFront($router);

        return $router;
    }

    protected static function buildAdmin(RouteList $router): RouteList
    {
        $list = new RouteList('Admin');
        $router->add($list);
        $list->addRoute('admin/<presenter>/<action>[/<id>]', 'Home:default');

        return $router;
    }

    protected static function buildFront(RouteList $router): RouteList
    {
        $list = new RouteList('Front');
        $router->add($list);
        $list->addRoute('<presenter>/<action>[/<id>]', 'Home:default');

        return $router;
    }

}
