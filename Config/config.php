<?php

return [
    'name'        => 'Netsuite',
    'description' => 'Netsuite integration',
    'author'      => 'kuzmany.biz',
    'version'     => '1.0.0',
    'services'    => [
        'events'       => [

        ],
        'models'       => [
        ],
        'forms'        => [

        ],
        'other'        => [

        ],
        'integrations' => [
            'mautic.integration.netsuite' => [
                'class'     => \MauticPlugin\MauticNetsuiteBundle\Integration\NetsuiteIntegration::class,
                'arguments' => [

                ],
            ],
        ],
    ],
];
