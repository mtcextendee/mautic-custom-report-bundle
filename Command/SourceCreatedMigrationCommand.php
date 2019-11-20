<?php

/*
 * @copyright   2019 Mautic Contributors. All rights reserved
 * @author      Mautic
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace MauticPlugin\MauticCustomReportBundle\Command;

use Doctrine\ORM\EntityManager;
use Mautic\CoreBundle\Command\ModeratedCommand;
use phpDocumentor\Reflection\Types\Parent_;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class SourceCreatedMigrationCommand extends ModeratedCommand
{
    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * SourceCreatedMigrationCommand constructor.
     *
     * @param EntityManager $entityManager
     */
    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('mautic:source:created:contacts:migration')
            ->setDescription('Created contacts source migration')
            ->setHelp('Created contacts source migration');

        parent::configure();
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $key = __CLASS__;
        if (!$this->checkRunStatus($input, $output, $key)) {
            return 0;
        }

        $maxIdQuery = 'SELECT MAX(ccl.log_id) FROM '.MAUTIC_TABLE_PREFIX.'custom_contact_log ccl';
        $maxLogId =  (int) $this->entityManager->getConnection()->query($maxIdQuery)->fetchColumn();

        $countQuery = 'SELECT COUNT(lel.id) FROM '.$this->getLeadEventLogQueryPart().' AND lel.id > '.$maxLogId;
        $numberOfImportedRows =   $this->entityManager->getConnection()->query($countQuery)->fetchColumn();

        $query = 'INSERT INTO '.MAUTIC_TABLE_PREFIX.'custom_contact_log (lead_id, log_id, url, date_added)
SELECT lel.lead_id, lel.id,ph.url,lel.date_added FROM '.$this->getLeadEventLogQueryPart();
        if ($maxLogId) {
            $query.= ' AND lel.id > '.$maxLogId;
        }
        $this->entityManager->getConnection()->query($query);

        $output->writeln(sprintf("Migrated %s lines", $numberOfImportedRows));
    }

    /**
     * @return string
     */
    private function getLeadEventLogQueryPart()
    {
        return MAUTIC_TABLE_PREFIX.'lead_event_log lel
 LEFT JOIN '.MAUTIC_TABLE_PREFIX.'page_hits ph ON ph.id = lel.object_id
WHERE lel.bundle = \'page\' and lel.object = \'hit\' AND lel.action = \'created_contact\'';
    }
}
