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

        $router[] = $list = new RouteList('main');
        $list[] = new Route('<presenter>/<action>[/<id>]', 'Home:default');

        return $router;
    }


}
