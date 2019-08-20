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
         * TWIG TEMPLATE CONFIGURATION
         * Adding this module's (Melis Platform Silex Demo Tool Logic) twig template directory to the Silex.
         */
        #Getting pre-configured twig template directory path/s
        $twigTemplatePath = $app['twig.path'];
        #Getting the twig template directory path to be added
        $twigPath = __DIR__.'/../Templates';
        #Merge the pre-configured twig template directory path/s with the new one.
        array_push($twigTemplatePath,$twigPath);
        #Setting twig template directory paths
        $app['twig.path'] = $twigTemplatePath;
        #Setting twig template to debug mode
        $app['twig.options'] = array("debug" => true);

        /**
         * DATABASE CONFIGURATION
         * Configuring Silex DB using Melis Platform DB configurations.
         */
        #Getting DB configurations from Melis Platform
        $dbConfig = include __DIR__ .  '/../../../../../config/autoload/platforms/' . getenv('MELIS_PLATFORM') . '.php';
        $dsn = str_getcsv($dbConfig['db']['dsn'],";");
        foreach ($dsn as $key => $config){
            if(strpos($config, ':') !== false)
                $data = explode("=",explode(":",$config)[1]);
            else
                $data = explode("=",$config);

            $dbConfig['db'][$data[0]] = $data[1];
        }

        #Getting pre configured DB configurations
        $dbObtions = isset($app['db.options']) ? $app['db.options'] : (isset($app['dbs.options']) ? $app['dbs.options'] : []);

        #Preparing DB configurations from the Melis Platform
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
            #Merging Silex DB Configuration if Silex has SINGLE DB configuration
            $melisDBOptions['silex'] = $dbObtions;
        }else{
            #Merging Silex DB Configuration if Silex has MULTIPLE DB configuration
            foreach(array_reverse($dbObtions[0],true) as $key => $dbObtion){
                $melisDBOptions[$key] = $dbObtion;
            }
        }
        $melisDBOptions = array_reverse($melisDBOptions);

        $app['dbs.options'] = $melisDBOptions;


        /**
         * ROUTING CONFIGURATIONS
         */
        #Silex routing DEMO configuration using data queries from database (MELIS PLATFORM DATABASE);
        $app->get('/albums', function () use ($app) {
            $sql = "SELECT * FROM album ";
            $albums = $app['dbs']['melis']->fetchAll($sql);
            return $app['twig']->render('albums.template.html.twig',array("albums" => $albums));
        });

        #Silex routing DEMO configuration using MELIS PLATFORM SERVICES;
        $app->get('/melis-news', function () use ($app) {
            $newsNewsService = $app['melis.services']->getService("MelisCmsNewsService");
            $news = $newsNewsService->getNewsList();
            return $app['twig']->render('news.template.html.twig',array("news" => $news));
        });


        /**
         * TRANSLATIONS CONFIGURATIONS
         */
        #Getting Translations from the Demo Logic translation directory
        $demoToolLogicEn = require __DIR__ .  '/../Translations/en_EN.interface.php';
        $demoToolLogicFr = require __DIR__ .  '/../Translations/fr_FR.interface.php';

        #Merging with existing Translations
        $demoToolLogicEn = array_merge( $demoToolLogicEn, !empty($app['translator.domains']['messages']['en']) ? $app['translator.domains']['messages']['en'] : []);
        $demoToolLogicFr = array_merge( $demoToolLogicFr, !empty($app['translator.domains']['messages']['fr']) ? $app['translator.domains']['messages']['fr'] : []);
        $app['twig'] = $app->extend('twig', function ($twig, $app) {
            // add custom globals, filters, tags, ...
            return $twig;
        });

        #Setting Translations
        $app['translator.domains'] = array(
            'messages' => array(
                'en' =>  $demoToolLogicEn,
                'fr' => $demoToolLogicFr,
            )
        );

        #Setting fallback translations
        $app['locale_fallbacks'] = array('en');
        #Setting translation locale currently used by the Melis Platform
        $app['locale'] = substr($_SESSION['meliscore']['melis-lang-locale'],0,2);

    }

    public function register(Container $app)
    {

    }

}