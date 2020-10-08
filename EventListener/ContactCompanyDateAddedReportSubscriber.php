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
use Mautic\LeadBundle\Model\CompanyReportData;
use Mautic\LeadBundle\Report\FieldsBuilder;
use Mautic\ReportBundle\Event\ReportBuilderEvent;
use Mautic\ReportBundle\Event\ReportGeneratorEvent;
use Mautic\ReportBundle\ReportEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ContactCompanyDateAddedReportSubscriber implements EventSubscriberInterface
{
    const CONTACT_COMPANY_DATEADDED = 'contact.company_date_added';

    /**
     * @var FieldsBuilder
     */
    private $fieldsBuilder;

    /**
     * @var CompanyReportData
     */
    private $companyReportData;

    /**
     * @param FieldsBuilder     $fieldsBuilder
     * @param CompanyReportData $companyReportData
     */
    public function __construct(FieldsBuilder $fieldsBuilder, CompanyReportData $companyReportData)
    {
        $this->fieldsBuilder = $fieldsBuilder;
        $this->companyReportData = $companyReportData;
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
        if (!$event->checkContext([self::CONTACT_COMPANY_DATEADDED])) {
            return;
        }

        $contactColumns = $this->fieldsBuilder->getLeadFieldsColumns('l.');
        $companyColumns = $this->companyReportData->getCompanyData();

        $stageColumns = [
            'l.stage_id'           => [
                'label' => 'mautic.lead.report.attribution.stage_id',
                'type'  => 'int',
                'link'  => 'mautic_stage_action',
            ],
            's.name'               => [
                'alias' => 'stage_name',
                'label' => 'mautic.lead.report.attribution.stage_name',
                'type'  => 'string',
            ],
            's.date_added' => [
                'alias'   => 'stage_date_added',
                'label'   => 'mautic.customreport.report.attribution.stage_date_added',
                'type'    => 'string',
                'formula' => '(SELECT MAX(stage_log.date_added) FROM '.MAUTIC_TABLE_PREFIX.'lead_stages_change_log stage_log WHERE stage_log.stage_id = l.stage_id AND stage_log.lead_id = l.id)',
            ],
        ];

        $columns = array_merge($contactColumns, $companyColumns, $stageColumns);

        $data = [
            'display_name' => 'mautic.customreport.report.contact.company_dateadded',
            'columns'      => $columns,
            'filters'      => $columns,
        ];
        $event->addTable(self::CONTACT_COMPANY_DATEADDED, $data, ReportSubscriber::GROUP_CONTACTS);

        unset($columns, $filters, $columns, $data);
    }

    /**
     * Initialize the QueryBuilder object to generate reports from.
     *
     * @param ReportGeneratorEvent $event
     */
    public function onReportGenerate(ReportGeneratorEvent $event)
    {
        if (!$event->checkContext([self::CONTACT_COMPANY_DATEADDED])) {
            return;
        }

        $qb = $event->getQueryBuilder();
        $qb->from(MAUTIC_TABLE_PREFIX.'leads', 'l');

        if ($event->hasColumn(['u.first_name', 'u.last_name']) || $event->hasFilter(['u.first_name', 'u.last_name'])) {
            $qb->leftJoin('l', MAUTIC_TABLE_PREFIX.'users', 'u', 'u.id = l.owner_id');
        }

        if ($event->hasColumn('i.ip_address') || $event->hasFilter('i.ip_address')) {
            $event->addLeadIpAddressLeftJoin($qb);
        }

        if ($event->hasColumn(['s.name']) || $event->hasFilter(['s.name'])) {
            $qb->leftJoin('l', MAUTIC_TABLE_PREFIX.'stages', 's', 's.id = l.stage_id');
        }

        if ($this->companyReportData->eventHasCompanyColumns($event)) {
            $event->addCompanyLeftJoin($qb);
        }

        $event->applyDateFilters($qb, 'date_added', 'companies_lead');

        $event->setQueryBuilder($qb);
    }
}
