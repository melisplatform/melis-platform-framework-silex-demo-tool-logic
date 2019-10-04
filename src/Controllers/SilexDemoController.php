<?php
namespace MelisPlatformFrameworkSilexDemoToolLogic\Controllers;

use Silex\Api\ControllerProviderInterface;
use Silex\Application;

class SilexDemoController implements ControllerProviderInterface {

    public function connect(Application $app) {
        $factory=$app['controllers_factory'];
        $factory->get('/silex-list','MelisPlatformFrameworkSilexDemoToolLogic\Controllers\SilexDemoController::silexDemo');
        $factory->get('/silex-plugin','MelisPlatformFrameworkSilexDemoToolLogic\Controllers\SilexDemoController::silexPlugin');
        return $factory;
    }

    public function silexDemo(Application $app) {
        #using MELIS PLATFORM SERVICES;
        $langSvc = $app['melis.services']->getService("MelisEngineLang");
        $langs = $langSvc->getAvailableLanguages();

        #using Melis Database;
        $sql = "SELECT * FROM melis_demo_album ";
        $albums = $app['dbs']['melis']->fetchAll($sql);

        return $app['twig']->render('demo.template.html.twig',array("albums" => $albums,"langs"=>$langs));
    }
    public function silexPlugin(Application $app) {

        #using Melis Database;
        $sql = "SELECT * FROM melis_demo_album ";
        $albums = $app['dbs']['melis']->fetchAll($sql);

        return $app['twig']->render('plugin.template.html.twig',array("albums" => $albums));
    }
}
