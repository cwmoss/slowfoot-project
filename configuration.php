<?php

use slowfoot\configuration;
use slowfoot\loader\dataset;

return new configuration(
    site_name: 'mumok Demo',
    site_description: 'look at beautiful works of art',
    sources: [
        'dataset' => new dataset(file: 'dataset-mumok.ndjson'),
        /*
        'sanity' => [
            'dataset' => 'production',
            'projectId' => $_ENV['SANITY_ID'],
            'useCdn' => true,
            // 'query' => '*[_type=="custom-type-query"]'
        ]
        */
        /*
        'csv' => [
            'file' => '../movie-dataset-archive/movies_metadata.csv',
            'jsol' => true,
            'type' => 'movie'
        ]*/
    ],
    templates: [
        'artist' => function ($obj) {
            return '/artist/' . URLify::filter($obj['firstname'] . ' ' . $obj['familyname'], 60, 'de');
        },
        'work' => [
            '/works/:_id',
            [
                'name' => 'en',
                'path' => '/works/:_id/en'
            ]
        ],
        'tag' => '/tag/:name',
        'newsletter' => '/newsletter/:slug.current',
        'gallery_page' => '/g/:slug.current',
        'movie' => '/movie/:_id'
        //fn ($doc) => 'newsletter/' . $doc['slug']['current']
    ],
    preview: [
        'sanity' => [
            'dataset' => 'production',
            'projectId' => $_ENV['SANITY_ID'],
            'useCdn' => false,
            //'withCredentials' => true,
            'token' => $_ENV['SANITY_TOKEN']
        ]
    ],
    assets: [
        'dir' => 'images',
        'path' => '/images',
        'profiles' => [
            'small' => [
                's' => '600x400',
                'mode' => 'fit'
            ],
            'gallery' => [
                's' => '700x',
                '4c' => ['creator' => 'Robbie Øfchen']
            ]
        ]
    ],
    hooks: [
        'on_load' => function ($row, $ds) {
            // [_id] => a-_a:325
            if (preg_match('/:/', $row['_id'])) {
                return null;
            }
            /*
                works.tags should become a object of type 'tag'
                make reverse reference from tag to works via tag.works
                this will be faster to query for tag pages
            */
            if ($row['tags']) {
                $tags = split_tags($row['tags']);
                $refs = [];
                foreach ($tags as $t) {
                    $name = URLify::filter($t, 60, 'de');
                    $title = $t;
                    $id = 't-' . $name;
                    $exists = $ds->get($id);
                    if ($exists) {
                        $ds->add_ref($id, 'works', $row);
                    } else {
                        $ds->add(
                            $id,
                            [
                                '_type' => 'tag',
                                'name' => $name,
                                'title' => $t,
                                'works' => [
                                    ['_ref' => $row['_id']]
                                ]
                            ]
                        );
                    }
                    $refs[] = ['_ref' => $id];
                }
                $row['tags'] = $refs;
            }
            return $row;
        }
    ]
);
