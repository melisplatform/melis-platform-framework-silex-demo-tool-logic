<?php
namespace MelisPlatformFrameworkSilexDemoToolLogic\Provider;
use MelisPlatformFrameworkSilexDemoToolLogic\Controllers\SilexDemoController;
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

        $app['twig'] = $app->extend('twig', function ($twig, $app) {
            // add custom globals, filters, tags, ...
            return $twig;
        });

        /**
         * ROUTING CONFIGURATIONS
         */
        #Silex routing DEMO configuration using data queries from database (MELIS PLATFORM DATABASE) and data from Melis Platform Services;
        $app->get('/melis-news-albums', function () use ($app) {
            #using MELIS PLATFORM SERVICES;
            $newsNewsService = $app['melis.services']->getService("MelisCmsNewsService");
            $news = $newsNewsService->getNewsList();

            $albums = [];
//            #using Melis Database;
//            $sql = "SELECT * FROM album ";
//            $albums = $app['dbs']['melis']->fetchAll($sql);
//            return $app['twig']->render('demo.template.html.twig',array("albums" => $albums,"news"=>$news));
        });

        #Silex routing DEMO configuration using a Silex Controller provider;
        $app->mount('/', new SilexDemoController());


        /**
         * TRANSLATIONS CONFIGURATIONS
         */
        #Getting Translations from the Demo Logic translation directory
        $demoToolLogicEn = file_exists(__DIR__ .  '/../Translations/en_EN.interface.php') ? require __DIR__ .  '/../Translations/en_EN.interface.php' : [];;
        $demoToolLogicFr = file_exists(__DIR__ .  '/../Translations/fr_FR.interface.php') ? require __DIR__ .  '/../Translations/fr_FR.interface.php' : [];;

        #Merging with existing Translations
        $demoToolLogicEn = array_merge( $demoToolLogicEn, !empty($app['translator.domains']['messages']['en']) ? $app['translator.domains']['messages']['en'] : []);
        $demoToolLogicFr = array_merge( $demoToolLogicFr, !empty($app['translator.domains']['messages']['fr']) ? $app['translator.domains']['messages']['fr'] : []);

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
