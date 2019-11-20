<?php

/*
 * @copyright   2019 MTCExtendee. All rights reserved
 * @author      MTCExtendee
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace MauticPlugin\MauticCustomReportBundle\Integration;

use Mautic\PluginBundle\Integration\AbstractIntegration;


class CustomReportIntegration extends AbstractIntegration
{
    const INTEGRATION_NAME = 'CustomReport';

    public function getName()
    {
        return self::INTEGRATION_NAME;
    }

    public function getDisplayName()
    {
        return 'Custom Report';
    }

    public function getAuthenticationType()
    {
        return 'none';
    }

    public function getRequiredKeyFields()
    {
        return [
        ];
    }

    public function getIcon()
    {
        return 'plugins/MauticCustomReportBundle/Assets/img/icon.png';
    }
}
