<?php
namespace MelisPlatformFrameworkSilexDemoToolLogic\Provider;
use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Silex\Api\BootableProviderInterface;
use Silex\Application;

/**
 * MelisSilexDemoTooolLogicServiceProvider
 *
 * This provider is contains configuration for external twig templates, DB configurations, Routing for Silex
 * which are used in Melis Platform Framework Silex Demo Tool.
 *
 */
class MelisSilexDemoTooolLogicServiceProvider implements BootableProviderInterface,ServiceProviderInterface
{
    public function boot(Application $app)
    {
        /**
         * Adding this module's (Melis Platform Silex Demo Tool Logic) twig template directory to the Silex.
         */
        $twigTemplatePath = $app['twig.path'];
        $twigPath = __DIR__.'/../Templates';
        array_push($twigTemplatePath,$twigPath);
        $app['twig.path'] = $twigTemplatePath;

        /**
         * Getting DB configurations from Melis Platform
         */
        $dbConfig = include __DIR__ .  '/../../../../../../config/autoload/platforms/' . getenv('MELIS_PLATFORM') . '.php';
        $dsn = str_getcsv($dbConfig['db']['dsn'],";");
        foreach ($dsn as $key => $config){
            if(strpos($config, ':') !== false)
                $data = explode("=",explode(":",$config)[1]);
            else
                $data = explode("=",$config);

            $dbConfig['db'][$data[0]] = $data[1];
        }

        /**
         * Configuring Silex DB using Melis Platform DB configurations.
         */
        $dbObtions = isset($app['db.options']) ? $app['db.options'] : (isset($app['dbs.options']) ? $app['dbs.options'] : []);
        $melisDBOptions = array(
            'melis' => array(
                'driver'   => 'pdo_mysql',
                'host'      => $dbConfig['db']['host'],
                'dbname'    => $dbConfig['db']['dbname'],
                'user'      => $dbConfig['db']['username'],
                'password'  => $dbConfig['db']['password'],
                'charset'   => $dbConfig['db']['charset'],
            )
        );

        if (count($dbObtions) == count($dbObtions, COUNT_RECURSIVE)){
            /**
             * Silex DB Configuration if Silex has SINGLE DB configuration
             */
            $melisDBOptions['silex'] = $dbObtions;
        }else{
            /**
             * Silex DB Configuration if Silex has MULTIPLE DB configuration
             */
            foreach(array_reverse($dbObtions[0],true) as $key => $dbObtion){
                $melisDBOptions[$key] = $dbObtion;
            }
        }

        $melisDBOptions = array_reverse($melisDBOptions);
        $app['dbs.options'] = $melisDBOptions;

        /**
         * Silex routing configuration for this silex module (Melis Platform Silex Demo Tool Logic).
         */
        $app->get('/albums', function () use ($app) {
            $sql = "SELECT * FROM album ";
            $albums = $app['dbs']['melis']->fetchAll($sql);
            return $app['twig']->render('albums.template.html.twig',array("albums" => $albums));
        });
    }

    public function register(Container $app)
    {

    }
}