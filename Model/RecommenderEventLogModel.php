<?php

/*
 * @copyright   2016 Mautic Contributors. All rights reserved
 * @author      Mautic
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace MauticPlugin\MauticRecommenderBundle\Model;

use Doctrine\DBAL\Query\QueryBuilder;
use Mautic\CoreBundle\Helper\Chart\ChartQuery;
use Mautic\CoreBundle\Helper\Chart\LineChart;
use Mautic\CoreBundle\Model\AbstractCommonModel;
use Mautic\CoreBundle\Model\AjaxLookupModelInterface;
use Mautic\CoreBundle\Model\FormModel;
use Mautic\CoreBundle\Model\TranslationModelTrait;
use Mautic\CoreBundle\Model\VariantModelTrait;
use MauticPlugin\MauticMTCPilotBundle\MTCPilotEvents;
use MauticPlugin\MauticMTCPilotBundle\Entity\MTCPilot;
use MauticPlugin\MauticMTCPilotBundle\Entity\MTCPilotRepository;
use MauticPlugin\MauticMTCPilotBundle\Entity\Stat;
use MauticPlugin\MauticMTCPilotBundle\Event\MTCPilotEvent;
use Mautic\LeadBundle\Entity\Lead;
use MauticPlugin\MauticRecommenderBundle\Entity\Recommender;
use MauticPlugin\MauticRecommenderBundle\Entity\RecommenderRepository;
use MauticPlugin\MauticRecommenderBundle\Event\RecommenderEvent;
use MauticPlugin\MauticRecommenderBundle\RecommenderEvents;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;

class RecommenderEventLogModel extends AbstractCommonModel
{

}