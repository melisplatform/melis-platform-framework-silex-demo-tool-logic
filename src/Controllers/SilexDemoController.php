<?php
namespace MelisPlatformFrameworkSilexDemoToolLogic\Controllers;

use Silex\Api\ControllerProviderInterface;
use Silex\Application;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class SilexDemoController implements ControllerProviderInterface {

    public function connect(Application $app) {
        $factory=$app['controllers_factory'];
        $factory->get('/silex-plugin','MelisPlatformFrameworkSilexDemoToolLogic\Controllers\SilexDemoController::silexPlugin');
        $factory->get('/melis/silex-list','MelisPlatformFrameworkSilexDemoToolLogic\Controllers\SilexDemoController::silexDemo');
        $factory->get('/melis/silex-album-form','MelisPlatformFrameworkSilexDemoToolLogic\Controllers\SilexDemoController::silexAlbumForm');
        $factory->post('/melis/silex-save-album','MelisPlatformFrameworkSilexDemoToolLogic\Controllers\SilexDemoController::silexSaveAlbum');
        $factory->post('/melis/silex-edit-album','MelisPlatformFrameworkSilexDemoToolLogic\Controllers\SilexDemoController::silexEditAlbum');
        $factory->post('/melis/silex-delete-album','MelisPlatformFrameworkSilexDemoToolLogic\Controllers\SilexDemoController::silexDeleteAlbum');
        $factory->post('/melis/silex-translation','MelisPlatformFrameworkSilexDemoToolLogic\Controllers\SilexDemoController::getTranslations');

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

    public function silexAlbumForm(Application $app, Request $request) {

        $params = !empty($request->query->get("parameters")) ? $request->query->get("parameters") : [];

        return $app['twig']->render('form.album.template.html.twig',array('alb' => $params));
    }

    public function silexSaveAlbum(Application $app, Request $request) {
        if(!empty($request->get("alb_id"))){
            $sql = "UPDATE melis_demo_album SET alb_name = ?, alb_song_num = ? WHERE alb_id = ?";
            $app['dbs']['melis']->executeUpdate($sql, array($request->get("alb_name"), $request->get("alb_song_num"), $request->get("alb_id")));
        }else {
            $app['dbs']['melis']->insert("melis_demo_album",array(
                "alb_name" => $request->get("alb_name"),
                "alb_song_num" => $request->get("alb_song_num")
            ));
        }
        return new JsonResponse(array("success" => 1,"title"=>"Create Album", "message" => "Album Saved", "errors" => []));

    }

    public function silexEditAlbum(Application $app, Request $request) {

        $sql = 'SELECT * FROM melis_demo_album WHERE alb_id = :id';
        $album = $app['dbs']['melis']->fetchAssoc($sql, array(
            'id' => $request->get('id'),
        ));
        return new JsonResponse(array("success" => 1,"title"=>"Edit Album","album" => $album, "message" => "Album Edited", "errors" => []));

    }

    public function silexDeleteAlbum(Application $app, Request $request) {

        $app['dbs']['melis']->delete("melis_demo_album",array(
            "alb_id" => $request->get("id")
        ));

        return new JsonResponse(array("success" => 1,"title"=>"Delete Album", "message" => "Album Deleted", "errors" => []));

    }

    public function getTranslations(Application $app) {

        $locale = empty( $app['locale']) ? "en" :  $app['locale'];
        $translation = $app['translator.domains']['messages'][$locale];

        return new JsonResponse(array("success" => 1, "translation" => $translation));

    }

    public function silexPlugin(Application $app) {

        #using Melis Database;
        $sql = "SELECT * FROM melis_demo_album ";
        $albums = $app['dbs']['melis']->fetchAll($sql);

        return $app['twig']->render('plugin.template.html.twig',array("albums" => $albums));
    }
}
