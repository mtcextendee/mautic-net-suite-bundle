<?php

return [
    'name'        => 'NetSuite',
    'description' => 'NetSuite integration',
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
                'class'     => \MauticPlugin\MauticNetSuiteBundle\Integration\NetSuiteIntegration::class,
                'arguments' => [

                ],
            ],
        ],
    ],
];
