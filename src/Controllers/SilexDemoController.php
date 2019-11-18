<?php
namespace MelisPlatformFrameworkSilexDemoToolLogic\Controllers;

use MelisCore\Service\MelisCoreFlashMessengerService;
use MelisPlatformFrameworkSilex\Service\MelisPlatformToolSilexService;
use Silex\Api\ControllerProviderInterface;
use Silex\Application;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;

class SilexDemoController implements ControllerProviderInterface {

    public function connect(Application $app) {
        $factory=$app['controllers_factory'];
        $factory->get('/silex-plugin','MelisPlatformFrameworkSilexDemoToolLogic\Controllers\SilexDemoController::silexPlugin');
        $factory->get('/melis/silex-list','MelisPlatformFrameworkSilexDemoToolLogic\Controllers\SilexDemoController::silexDemo');
        $factory->get('/melis/silex-album-form','MelisPlatformFrameworkSilexDemoToolLogic\Controllers\SilexDemoController::silexAlbumForm');
        $factory->post('/melis/silex-table-fetch-album','MelisPlatformFrameworkSilexDemoToolLogic\Controllers\SilexDemoController::silexFetchAlbums');
        $factory->post('/melis/silex-save-album','MelisPlatformFrameworkSilexDemoToolLogic\Controllers\SilexDemoController::silexSaveAlbum');
        $factory->post('/melis/silex-edit-album','MelisPlatformFrameworkSilexDemoToolLogic\Controllers\SilexDemoController::silexEditAlbum');
        $factory->post('/melis/silex-delete-album','MelisPlatformFrameworkSilexDemoToolLogic\Controllers\SilexDemoController::silexDeleteAlbum');
        $factory->post('/melis/silex-translation','MelisPlatformFrameworkSilexDemoToolLogic\Controllers\SilexDemoController::getTranslations');

        return $factory;
    }

    /**
     *
     * @param Application $app
     * @return mixed
     *
     * Renders the Silex Demo Tool Album content view.
     */

    public function silexDemo(Application $app) {
        //getting data from melis db using MELIS PLATFORM SERVICES;
        $langSvc = $app['melis.services']->getService("MelisEngineLang");
        $langs = $langSvc->getAvailableLanguages();

        //This block of code below is the configuration of the data table that is same as the melis platform

        //getting config
        $config = include_once __DIR__."/../../config/MelisPlatfoformSilexAlbumTable.config.php";

        //Translate column names
        foreach ($config['table']['columns'] as $key => $column){
            $config['table']['columns'][$key]['text'] = $app['translator']->trans($column["text"]);
        }

        return $app['twig']->render('demo.template.html.twig',array("langs" => $langs, "silexTableConfig" => $config['table']));
    }

    /**
     * @param Application $app
     * @param Request $request
     * @return JsonResponse fetch the list of albums from melis DB for the data table.
     *
     * fetch the list of albums from melis DB for the data table.
     * @throws \Exception
     */
    public function silexFetchAlbums(Application $app, Request $request){

        $tableData = array();

        //Data table config
        $config = include_once __DIR__."/../../config/MelisPlatfoformSilexAlbumTable.config.php";

        $params = $request->request->all();

        // sorting ASC or DESC
        $sortOrder = $params['order'][0]['dir'] ?? null;
        // column to sort
        $selCol    = $params['order'] ?? null;
        $colId     = array_keys($config['table']['columns']);
        $selCol    = $colId[$selCol[0]['column']] ?? null;
        // number of displayed item per page
        $draw      = $params['draw'] ?? null;
        // pagination start
        $start     = $params['start'] ?? null;
        // drop down limit
        $length    = $params['length'] ?? null;
        // search value from the table
        $search    = $params['search']['value'] ?? null;
        // get all searchable columns from the config
        $searchableCols = $config['table']['searchables'] ?? [];
        // get data from the service

        try {
            // fetching albums depending on the filters applied to the table
            $qb = new \Doctrine\DBAL\Query\QueryBuilder($app['dbs']['melis']);
            $qb->select("*");
            $qb->from("melis_demo_album");
            if (! empty($searchableCols) && !empty($search)){
                foreach ($searchableCols as $idx => $col) {
                    $expr = $qb->expr();
                    $qb->orWhere($expr->like($col, "'%" . $search . "%'"));
                }
            }
            $qb->setFirstResult($start)
                ->setMaxResults($length)
                ->orderBy($selCol,$sortOrder);

            $data = $qb->execute()->fetchAll();

        }catch (\Exception $err) {
            // return error
            throw new \Exception($err->getMessage());
        }


        if (! empty($searchableCols) && !empty($search)) {
            $tmpDataCount = count($data);
        }else{
            $sql = "SELECT * FROM melis_demo_album ";
            $tmpDataCount = count($app['dbs']['melis']->fetchAll($sql));
        }
        $data = [
            'data' => $data,
            'dataCount' => $tmpDataCount
        ];

        // get total count of the data in the db
        $dataCount = $data['dataCount'];
        $albumData = $data['data'];
        // organized data
        $c = 0;

        foreach($albumData as $data){

            $data = (object)$data;

            $tableData[$c]['DT_RowId'] = $data->alb_id;
            $tableData[$c]['alb_id'] = $data->alb_id;
            $tableData[$c]['alb_name'] = $data->alb_name;
            $tableData[$c]['alb_date'] = $data->alb_date;
            $tableData[$c]['alb_song_num'] = $data->alb_song_num;
            $c++;
        }

        return new JsonResponse(array(
            'draw' => $draw,
            'recordsTotal' => $dataCount,
            'recordsFiltered' => $dataCount,
            'data' => $tableData
        ));
    }

    /**
     * @param Application $app
     * @param Request $request
     * @return mixed
     *
     * render the silex demo tool album form modal for creating/editing
     */
    public function silexAlbumForm(Application $app, Request $request) {

        $params = !empty($request->query->get("parameters")) ? $request->query->get("parameters") : [];

        return $app['twig']->render('form.album.template.html.twig',array('alb' => $params));
    }

    /**
     * @param Application $app
     * @param Request $request
     * @return JsonResponse save the album either creating or editing
     *
     * save the album either creating or editing
     * @throws \Exception
     */
    public function silexSaveAlbum(Application $app, Request $request) {

        $message = $app['translator']->trans("tr_meliscodeexamplesilex_tool_save_album_ko");
        $errors = [];
        $icon = MelisCoreFlashMessengerService::WARNING;

        // post data
        $album = array(
            'alb_name' => !empty($request->get("alb_name")) ? $request->get("alb_name") : null,
            'alb_song_num' => !empty($request->get("alb_song_num")) ? $request->get("alb_song_num") : null,
            'alb_id' => !empty($request->get("alb_id")) ? $request->get("alb_id") : null
        );

        // validation
        $constraint = new Assert\Collection(array(
            'alb_name' => new Assert\NotBlank(),
            'alb_song_num' => new Assert\NotBlank,
            'alb_id' => new Assert\Type('integer')
        ));
        $validatorResults = $app['validator']->validate($album, $constraint);

        // constructing validation result for melis compatibility
        // format of validation report for melis below.
        // array(
        //     "input_name1" => "error message.",
        //     "input_name2" => "error message.",
        // )

        foreach ($validatorResults as $validatorResult){
            $errors[str_replace(['[',']'],"",$validatorResult->getPropertyPath())] = $validatorResult->getMessage();
        }

        if(!empty($request->get("alb_id"))){

            // updating album
            $title = $app['translator']->trans("tr_meliscodeexamplesilex_tool_edit_album");
            if(empty($errors))
            {
                try {
                    $id = $album["alb_id"];
                    unset($album["alb_id"]);

                    $qb = new \Doctrine\DBAL\Query\QueryBuilder($app['dbs']['melis']);
                    $qb->update("melis_demo_album");

                    foreach($album as $key => $alb)
                        $qb->set($key,"'".$alb."'");

                    $qb->where("alb_id =".$id);
                    $qb->execute();

                    $success = 1;
                }catch (\Exception $err) {
                    // return error
                    throw new \Exception($err->getMessage());
                }
            }else{
                $success = 0;
            }


            if($success > 0){
                $message = $app['translator']->trans("tr_meliscodeexamplesilex_tool_save_album_ok");
                $icon = MelisCoreFlashMessengerService::INFO;
                $success = 1;
            }

            $id = $request->get("alb_id");
            $this->melisLog($app,$title,$message,$success,"SILEX_ALBUM_EDIT",$id);
            $this->melisNotification($app,$title,$message,$icon);

        }else {
            // creating album
            $title = $app['translator']->trans("tr_meliscodeexamplesilex_tool_new_album");
            if(!$errors) {
                try {
                    unset($album['alb_id']);
                    $success = $app['dbs']['melis']->insert("melis_demo_album", $album);
                } catch (\Exception $err) {
                    // return error
                    throw new \Exception($err->getMessage());
                }
            }else{
                $success = 0;
            }

            if($success > 0){
                $message = $app['translator']->trans("tr_meliscodeexamplesilex_tool_save_album_ok");
                $icon = MelisCoreFlashMessengerService::INFO;
                $success = 1;
            }

            $id = $app['dbs']['melis']->lastInsertId();
            $this->melisLog($app,$title,$message,$success,"SILEX_ALBUM_CREATE",$id);
            $this->melisNotification($app,$title,$message,$icon);

        }

        return new JsonResponse(array(
            "success" => $success,
            "title" => $title,
            "message" => $message,
            "errors" => $errors
        ));

    }

    /**
     * @param Application $app
     * @param Request $request
     * @return JsonResponse fetching data of the album to be edited
     *
     * fetching data of the album to be edited
     * @throws \Exception
     */
    public function silexEditAlbum(Application $app, Request $request) {

        try{
            // fetching data of album to be deleted
            $sql = 'SELECT * FROM melis_demo_album WHERE alb_id = :id';
            $album = $app['dbs']['melis']->fetchAssoc($sql, array(
                'id' => $request->get('id'),
            ));
        }catch (\Exception $err) {
            // return error
            throw new \Exception($err->getMessage());
        }

        $success = count($album) > 1 ? 1 : 0;

        return new JsonResponse(array(
            "success" => $success,
            "album" => $album,
        ));

    }

    /**
     * @param Application $app
     * @param Request $request
     * @return JsonResponse fetching the album data to be deleted
     *
     * fetching the album data to be deleted
     * @throws \Exception
     */
    public function silexDeleteAlbum(Application $app, Request $request) {

        $message = $app['translator']->trans("tr_meliscodeexamplesilex_tool_delete_album_ko");
        $title = $app['translator']->trans("tr_meliscodeexamplesilex_album_delete");
        $icon = MelisCoreFlashMessengerService::WARNING;

        try{
            $success = $app['dbs']['melis']->delete("melis_demo_album",array(
                "alb_id" => $request->get("id")
            ));
        }catch (\Exception $err) {
            // return error
            throw new \Exception($err->getMessage());
        }

        if($success > 0){

            $icon = MelisCoreFlashMessengerService::INFO;
            $message = $app['translator']->trans("tr_meliscodeexamplesilex_tool_delete_album_ok");
            $success = 1;

        }

        $id = $request->get('id');
        $this->melisLog($app,$title,$message,$success,"SILEX_ALBUM_CREATE",$id);
        $this->melisNotification($app,$title,$message,$icon);

        return new JsonResponse(array(
            "success" => $success,
            "title" => $title,
            "message" => $message
        ));

    }

    /**
     * @param Application $app
     * @return JsonResponse
     *
     * fetch all the translation using silex
     */
    public function getTranslations(Application $app) {

        $locale = empty( $app['locale']) ? "en" :  $app['locale'];
        $translation = $app['translator.domains']['messages'][$locale];

        return new JsonResponse(array(
            "success" => 1,
            "translation" => $translation
        ));

    }

    /**
     * @param Application $app
     * @return mixed fetch album data from melis DB for the silex demo tool templating plugin
     *
     * fetch album data from melis DB for the silex demo tool templating plugin
     * @throws \Exception
     */
    public function silexPlugin(Application $app) {

        try {
            #using Melis Database;
            $sql = "SELECT * FROM melis_demo_album ";
            $albums = $app['dbs']['melis']->fetchAll($sql);
        }catch (\Exception $err) {
            // return error
            throw new \Exception($err->getMessage());
        }

        return $app['twig']->render('plugin.template.html.twig',array("albums" => $albums));
    }

    /**
     * @param Application $app silex application
     * @param string $title log title
     * @param string $message string log message
     * @param string $success action status
     * @param string $typeCode action log code
     * @param int $itemId id of the modified or created data
     *
     * logs action made in silex demo tool album in melis log module.
     */
    private function melisLog($app,$title,$message,$success,$typeCode,$itemId){
        $logSrv = $app['melis.services']->getService("MelisCoreLogService");
        $logSrv->saveLog($title, $message, $success, $typeCode, $itemId);
    }

    /**
     * @param Application $app
     * @param string $title
     * @param string $message
     * @param string $icon
     *
     *
     * add action made to the melis notification.
     */
    private function melisNotification($app, $title,$message,$icon = MelisCoreFlashMessengerService::INFO){

        $flashMessenger =  $app['melis.services']->getService('MelisCoreFlashMessenger');
        $flashMessenger->addToFlashMessenger($title, $message, $icon);
    }
}
