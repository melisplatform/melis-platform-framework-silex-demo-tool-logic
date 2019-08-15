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
        //Add the module's twig template directory to the silex.
        $twigTemplatePath = $app['twig.path'];
        $twigPath = __DIR__.'/../Templates';
        array_push($twigTemplatePath,$twigPath);
        $app['twig.path'] = $twigTemplatePath;

        $app->get('/albums', function () use ($app) {
            $sql = "SELECT * FROM album ";
            $albums = $app['db']->fetchAll($sql);
            return $app['twig']->render('albums.template.html.twig',array("albums" => $albums));
        });
    }

    public function register(Container $app)
    {

    }
}