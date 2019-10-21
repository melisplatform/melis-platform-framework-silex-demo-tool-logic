<?php
return array(
    'table' => array(
        'target' => '#silexDemoToolAlbumTable',
        'ajaxUrl' => '/melis/silex-table-fetch-album',
        'dataFunction' => '',
        'ajaxCallback' => '',
        'filters' => array(
            'left' => array(
                'show' => [
                    'view' => 'demo.tool.album.table.filter.limit.html.twig'
                ],
            ),
            'center' => array(
                'search' => [
                    'view' => 'demo.tool.album.table.filter.search.html.twig'
                ]
            ),
            'right' => array(
                'refresh' => [
                    'view' => 'demo.tool.album.table.refresh.html.twig'
                ]
            ),
        ),
        'columns' => array(
            'alb_id' => array(
                'text' => 'ID',
                'css' => array('width' => '10%', 'padding-right' => '0'),
                'sortable' => true,
            ),
            'alb_name' => array(
                'text' => 'tr_melis_lumen_table1_heading_name',
                'css' => array('width' => '20%', 'padding-right' => '0'),
                'sortable' => true,
            ),
            'alb_song_num' => array(
                'text' => 'tr_melis_lumen_table1_heading_songs',
                'css' => array('width' => '30%', 'padding-right' => '0'),
                'sortable' => true,
            ),
            'alb_date' => array(
                'text' => 'Date',
                'css' => array('width' => '30%', 'padding-right' => '0'),
                'sortable' => true,
            ),
        ),
        'searchables' => array(
            'alb_id','alb_name','alb_song_num', 'alb_date'
        ),
        'actionButtons' => array(
            'edit' => [
                'view' => 'demo.tool.album.table.btn.edit.html.twig',
            ],
            'delete' => [
                'view' => 'demo.tool.album.table.btn.delete.html.twig'
            ]
        ),
    ),
);