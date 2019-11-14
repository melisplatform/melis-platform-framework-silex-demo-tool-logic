<?php
return array(
    'table' => array(
        'ajaxUrl' => '/melis/silex-table-fetch-album',
        'dataFunction' => '',
        'ajaxCallback' => '',
        'attributes' => [
            'id' => 'silexDemoToolAlbumTable',
            'class' => 'table table-stripes table-primary dt-responsive nowrap',
            'cellspacing' => '0',
            'width' => '100%',
        ],
        'filters' => array(
            'left' => array(
                'show' => "l",
            ),
            'center' => array(
                'search' => "f"
            ),
            'right' => array(
                'refresh' => '<a class="btn btn-default silexDemoToolAlbumTableRefreshBtn" data-toggle="tab" aria-expanded="true" title="refresh"><i class="fa fa-refresh"></i></a>'
            ),
        ),
        'columns' => array(
            'alb_id' => array(
                'text' => "tr_meliscodeexamplesilex_album_id",
                'css' => array('width' => '10%', 'padding-right' => '0'),
                'sortable' => true,
            ),
            'alb_name' => array(
                'text' => "tr_meliscodeexamplesilex_album_title",
                'css' => array('width' => '20%', 'padding-right' => '0'),
                'sortable' => true,
            ),
            'alb_song_num' => array(
                'text' => "tr_meliscodeexamplesilex_album_songs",
                'css' => array('width' => '30%', 'padding-right' => '0'),
                'sortable' => true,
            ),
            'alb_date' => array(
                'text' => "tr_meliscodeexamplesilex_album_date",
                'css' => array('width' => '30%', 'padding-right' => '0'),
                'sortable' => true,
            ),
        ),
        'searchables' => array(
            'alb_id','alb_name','alb_song_num', 'alb_date'
        ),
        'actionButtons' => array(
            'edit' => '<button class="btn btn-success btn_meliscodeexamplesilex_edit" title="Edit"><i class="fa fa-pencil"></i></button>',
            'delete' => '<button class="btn btn-danger btn_meliscodeexamplesilex_delete" title="Delete"><i class="fa fa-times"></i></button>'
        ),
    ),
);