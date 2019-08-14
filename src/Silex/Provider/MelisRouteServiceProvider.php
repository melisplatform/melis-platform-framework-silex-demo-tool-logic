<?php
namespace Silex\Provider;
use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Silex\Api\BootableProviderInterface;
use Silex\Application;

/**
 * Melis Route Service Provider
 *
 * This is a provider where silex routes are configured
 *
 */
class MelisRouteServiceProvider implements BootableProviderInterface,ServiceProviderInterface
{


    public function boot(Application $app)
    {
        //Silex Routes configuration
        $app->get('/melis-news', function () use ($app) {
            $news = $app['MelisNews']->getNewsList();
            return $app['twig']->render('news.html.twig',array("news" => $news));
        });
    }

    public function register(Container $app)
    {

    }
}