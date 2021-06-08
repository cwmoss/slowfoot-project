<?php

return [
    'site_name' => 'mumok Demo',
    'site_description' => 'look at beautiful works of art',
    'site_url' => '',
    // TODO: solve genenv vs ENV problem
    'path_prefix' => getenv('PATH_PREFIX') ?: $_ENV['PATH_PREFIX'] ?: '',
    'title_template' => '',
    'sources' => [
        'dataset' => 'dataset-mumok.ndjson',
        /* 'sanity' => [
            'dataset' => 'production',
            'projectId' => $_ENV['SANITY_ID'],
            'useCdn' => true
            ]
        */
    ],
    'preview' => [
        'sanity' => [
            'dataset' => 'production',
            'projectId' => $_ENV['SANITY_ID'],
            'useCdn' => false,
            //'withCredentials' => true,
            'token' => $_ENV['SANITY_TOKEN']
        ]
    ],
    'templates' => [
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
        'newsletter' => '/newsletter/:slug.current'
        //fn ($doc) => 'newsletter/' . $doc['slug']['current']
    ],
    'hooks' => [
        'on_load' => function ($row, $ds) {
            // [_id] => a-_a:325
            if (preg_match('/:/', $row['_id'])) {
                return null;
            }
            if ($row['tags']) {
                $tags = split_tags($row['tags']);
                $refs = [];
                foreach ($tags as $t) {
                    $name = URLify::filter($t, 60, 'de');
                    $title = $t;
                    $id = 't-' . $name;
                    $exists = $ds->get($id);
                    if ($exists) {
                        dbg('++ add tag', $id);
                        $exists['works'][] = ['_ref' => $row['_id']];
                        $ds->update($id, $exists);
                    } else {
                        $ds->add(
                            $id,
                            [
                                '_id' => $id,
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
];
