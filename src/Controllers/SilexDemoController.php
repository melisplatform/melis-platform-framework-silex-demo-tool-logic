<?php
namespace MelisPlatformFrameworkSilexDemoToolLogic\Controllers;

use Silex\Api\ControllerProviderInterface;
use Silex\Application;

class SilexDemoController implements ControllerProviderInterface {

    public function connect(Application $app) {
        $factory=$app['controllers_factory'];
        $factory->get('/silex-demo','MelisPlatformFrameworkSilexDemoToolLogic\Controllers\SilexDemoController::silexDemo');
        return $factory;
    }

    public function silexDemo(Application $app) {
        #using MELIS PLATFORM SERVICES;
        $newsNewsService = $app['melis.services']->getService("MelisCmsNewsService");
        $news = $newsNewsService->getNewsList();

        #using Melis Database;
        $sql = "SELECT * FROM album ";
        $albums = $app['dbs']['melis']->fetchAll($sql);

        return $app['twig']->render('demo.template.html.twig',array("albums" => $albums,"news"=>$news));
    }
}