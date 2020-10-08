<?php

/*
 * @copyright   2019 Mautic Contributors. All rights reserved
 * @author      Mautic
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace MauticPlugin\MauticCustomReportBundle\EventListener;

use Mautic\LeadBundle\EventListener\ReportSubscriber;
use Mautic\LeadBundle\Report\FieldsBuilder;
use Mautic\ReportBundle\Event\ReportBuilderEvent;
use Mautic\ReportBundle\Event\ReportGeneratorEvent;
use Mautic\ReportBundle\ReportEvents;
use MauticPlugin\MauticCustomReportBundle\Entity\CustomCreatedContactLog;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class SourceCreatedContactReportSubscriber implements EventSubscriberInterface
{
    const SOURCE_CREATED_CONTACT = 'source.created_contact';

    /**
     * @var FieldsBuilder
     */
    private $fieldsBuilder;

    /**
     * @param FieldsBuilder $fieldsBuilder
     */
    public function __construct(FieldsBuilder $fieldsBuilder)
    {
        $this->fieldsBuilder = $fieldsBuilder;
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            ReportEvents::REPORT_ON_BUILD    => ['onReportBuilder', 0],
            ReportEvents::REPORT_ON_GENERATE => ['onReportGenerate', 0],
        ];
    }

    /**
     * Add available tables and columns to the report builder lookup.
     *
     * @param ReportBuilderEvent $event
     */
    public function onReportBuilder(ReportBuilderEvent $event)
    {
        if (!$event->checkContext([self::SOURCE_CREATED_CONTACT])) {
            return;
        }

        $columns = $this->fieldsBuilder->getLeadFieldsColumns('l.');


        $addColumns = [
            'ccl.url' => [
                'label' => 'mautic.customreport.report.source.created_contact',
                'type'  => 'string',
            ],
            'ccl.date_added' => [
                'label' => 'mautic.customreport.report.date_added',
                'type'  => 'datetime',
            ],
            'hits' => [
                'formula'=>'(SELECT COUNT(ccl2.url) FROM '.MAUTIC_TABLE_PREFIX.CustomCreatedContactLog::TABLE.' ccl2 
INNER JOIN '.MAUTIC_TABLE_PREFIX.'leads l ON l.id = ccl2.lead_id AND l.email IS NOT NULL
WHERE ccl2.date_added BETWEEN :dateFrom AND :dateTo AND ccl2.url = ccl.url)',
                'label' => 'mautic.customreport.report.hits',
                'type'  => 'int',
            ],
        ];

        $data = [
            'display_name' => 'mautic.customreport.report.source.created_contact',
            'columns'      => array_merge($columns, $addColumns),
            'filters'      => $columns,
        ];
        $event->addTable(self::SOURCE_CREATED_CONTACT, $data, ReportSubscriber::GROUP_CONTACTS);

        unset($columns, $filters, $columns, $data);
    }

    /**
     * Initialize the QueryBuilder object to generate reports from.
     *
     * @param ReportGeneratorEvent $event
     */
    public function onReportGenerate(ReportGeneratorEvent $event)
    {
        if (!$event->checkContext([self::SOURCE_CREATED_CONTACT])) {
            return;
        }

        $qb = $event->getQueryBuilder();
        $qb->from(MAUTIC_TABLE_PREFIX.CustomCreatedContactLog::TABLE, 'ccl');
        $qb->innerJoin('ccl',MAUTIC_TABLE_PREFIX.'leads', 'l', 'l.id = ccl.lead_id AND l.email IS NOT NULL');


        if (empty($event->getReport()->getGroupBy())) {
            $qb->addGroupBy('ccl.url');
            $qb->addGroupBy('l.id');
        }

        if (empty($event->getReport()->getOrderColumns())) {
            $qb->addOrderBy('hits1','DESC');
            $qb->addOrderBy('l.points','DESC');
        }

        $event->applyDateFilters($qb, 'date_added', 'ccl');

        $event->setQueryBuilder($qb);
    }
}
