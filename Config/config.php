<?php

return [
    'name'        => 'MauticCustomReportBundle',
    'description' => 'Custom reports for Mautic',
    'version'     => '1.0',
    'author'      => 'MTCExtendee',

    'routes' => [
    ],

    'services'   => [
        'events'=>[
            'mautic.customreport.created_contact' => [
                'class'     => \MauticPlugin\MauticCustomReportBundle\EventListener\SourceCreatedContactReportSubscriber::class,
                'arguments' => [
                    'mautic.lead.reportbundle.fields_builder',
                ],
            ],
            'mautic.customreport.contact_company_date_added' => [
                'class'     => \MauticPlugin\MauticCustomReportBundle\EventListener\ContactCompanyDateAddedReportSubscriber::class,
                'arguments' => [
                    'mautic.lead.reportbundle.fields_builder',
                    'mautic.lead.model.company_report_data'
                ],
            ],
        ],
        'forms'        => [
        ],
        'models'       => [

        ],
        'integrations' => [
            'mautic.integration.customreport' => [
                'class'     => \MauticPlugin\MauticCustomReportBundle\Integration\CustomReportIntegration::class,
                'arguments' => [
                ],
            ],
        ],
        'others'       => [

        ],
        'controllers'  => [
        ],
        'commands'=>[
            'mautic.customreport.command' => [
                'class'     => \MauticPlugin\MauticCustomReportBundle\Command\SourceCreatedMigrationCommand::class,
                'arguments' => [
                    'doctrine.orm.entity_manager'
                ],
                'tag'       => 'console.command',
            ],
        ]
    ],
    'parameters' => [
    ],
];
