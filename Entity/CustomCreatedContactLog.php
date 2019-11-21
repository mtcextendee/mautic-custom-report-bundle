<?php

/*
 * @copyright   2019 Mautic Contributors. All rights reserved
 * @author      Mautic
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace MauticPlugin\MauticCustomReportBundle\Entity;

use Doctrine\DBAL\Types\Type;
use Doctrine\ORM\Mapping as ORM;
use Mautic\ApiBundle\Serializer\Driver\ApiMetadataDriver;
use Mautic\CoreBundle\Doctrine\Mapping\ClassMetadataBuilder;
use Mautic\LeadBundle\Entity\Lead;
use Mautic\LeadBundle\Entity\LeadEventLog;
use phpDocumentor\Reflection\Types\Self_;

class CustomCreatedContactLog
{
    CONST TABLE = 'custom_created_contact_log';
    /**
     * @var int
     */
    protected $id;

    /**
     * @var \DateTime
     */
    protected $dateAdded;

    /**
     * @var string
     */
    protected $url;

    /**
     * @var Lead
     */
    protected $lead;

    /**
     * @var LeadEventLog
     */
    protected $log;


    public function __construct()
    {
        $this->setDateAdded(new \DateTime());
    }

    /**
     * @param ORM\ClassMetadata $metadata
     */
    public static function loadMetadata(ORM\ClassMetadata $metadata)
    {
        $builder = new ClassMetadataBuilder($metadata);
        $builder->setTable(self::TABLE)
            ->setCustomRepositoryClass(CustomCreatedContactLogRepository::class)
            ->addId()
            ->addIndex(['url', 'date_added'], 'url_date_added')
            ->addIndex(['date_added'], 'date_added');

        $builder->createManyToOne(
            'lead',
            'Mautic\LeadBundle\Entity\Lead'
        )->addJoinColumn('lead_id', 'id', true, false, 'SET NULL')
            ->cascadePersist()
            ->build();

        $builder->createManyToOne(
            'log',
            'Mautic\LeadBundle\Entity\LeadEventLog'
        )->addJoinColumn('log_id', 'id', true, false, 'SET NULL')
            ->cascadePersist()
            ->build();

        $builder->addField('url', Type::STRING, ['length' => 728]);
        $builder->addNamedField('dateAdded', 'datetime', 'date_added');

    }


    /**
     * Prepares the metadata for API usage.
     *
     * @param $metadata
     */
    public static function loadApiMetadata(ApiMetadataDriver $metadata)
    {
        $metadata->setGroupPrefix(self::TABLE)
            ->addListProperties(
                [
                    'id',
                    'dateAdded',
                ]
            )
            ->build();
    }


    /**
     * Get id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /*
     * Set dateAdded.
     *
     * @param \DateTime $dateAdded
     *
     * @return LeadEventLog
     */
    public function setDateAdded($dateAdded)
    {
        $this->dateAdded = $dateAdded;

        return $this;
    }

    /**
     * Get dateAdded.
     *
     * @return \DateTime
     */
    public function getDateAdded()
    {
        return $this->dateAdded;
    }


    public function getCreatedBy()
    {

    }

    public function getHeader()
    {

    }

    public function getPublishStatus()
    {

    }

    /**
     * @param string $url
     *
     * @return CustomCreatedContactLog
     */
    public function setUrl($url)
    {
        $this->url = $url;

        return $this;
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @param Lead $lead
     *
     * @return CustomCreatedContactLog
     */
    public function setLead($lead)
    {
        $this->lead = $lead;

        return $this;
    }

    /**
     * @return Lead
     */
    public function getLead()
    {
        return $this->lead;
    }

    /**
     * @param LeadEventLog $log
     *
     * @return CustomCreatedContactLog
     */
    public function setLog($log)
    {
        $this->log = $log;

        return $this;
    }

    /**
     * @return LeadEventLog
     */
    public function getLog()
    {
        return $this->log;
    }


}
