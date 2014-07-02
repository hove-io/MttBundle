<?php

/**
 * Symfony service to call the pdfGenerator webservice
 *
 * @author vdegroote
 */
namespace CanalTP\MttBundle\Services;

use Doctrine\Common\Persistence\ObjectManager;

use CanalTP\MttBundle\Entity\StopPoint;
use CanalTP\MttBundle\Entity\AmqpTask;

class PdfGenCompletionLib
{
    private $om = null;
    private $mediaManager = null;
    private $container = null;
    private $lineConfigRepo = null;
    private $timetableRepo = null;
    private $stopPointRepo = null;

    public function __construct(ObjectManager $om, MediaManager $mediaManager, $container)
    {
        $this->om = $om;
        $this->mediaManager = $mediaManager;
        $this->container = $container;
        $this->lineConfigRepo = $this->om->getRepository('CanalTPMttBundle:LineConfig');
        $this->timetableRepo = $this->om->getRepository('CanalTPMttBundle:Timetable');
        $this->stopPointRepo = $this->om->getRepository('CanalTPMttBundle:StopPoint');
    }

    private function getLineConfig($ack, $lineConfig)
    {
        if (
            $lineConfig == false ||
            // check if this ack is for a different lineConfig than the previous one
            $lineConfig->getExternalLineId() != $ack->getPayload()->timetableParams->externalLineId
        ) {
            $lineConfig = $this->lineConfigRepo->findOneBy(
                array(
                    'externalLineId' => $ack->getPayload()->timetableParams->externalLineId,
                    'season' => $ack->getPayload()->timetableParams->seasonId
                )
            );
        }

        return $lineConfig;
    }

    private function getTimetable($ack, $lineConfig, $timetable)
    {
        if ($timetable == false ||
            // check if this ack is for a different timetable than the previous one
            $timetable->getExternalRouteId() != $ack->getPayload()->timetableParams->externalRouteId
        ) {
            $timetable = $this->timetableRepo->findOneBy(
                array(
                    'externalRouteId' => $ack->getPayload()->timetableParams->externalRouteId,
                    'line_config' => $lineConfig->getId()
                )
            );
        }

        return $timetable;
    }

    private function getStopPoint($ack, $timetable)
    {
        return $this->stopPointRepo->findOneBy(
            array(
                'timetable' => $timetable->getId(),
                'externalId' => $ack->getPayload()->timetableParams->externalStopPointId
            )
        );
    }

    private function getSeason($seasonId)
    {
        $season = $this->om->getRepository('CanalTPMttBundle:Season')->find($seasonId);
        $this->om->refresh($season);

        return $season;
    }

    // save hash and pdfGeneration date returned by acks
    private function commit($task)
    {
        $lineConfig = false;
        $timetable = false;
        foreach ($task->getAmqpAcks() as $ack) {
            try {
                if ($ack->getPayload()->generated == true) {
                    $lineConfig = $this->getLineConfig($ack, $lineConfig);
                    $timetable = $this->getTimetable($ack, $lineConfig, $timetable);
                    $stopPoint = $this->getStopPoint($ack, $timetable);
                    if (empty($stopPoint)) {
                        $stopPoint = new StopPoint();
                        $stopPoint->setTimetable($timetable);
                        $stopPoint->setExternalId($ack->getPayload()->timetableParams->externalStopPointId);
                    }
                    $stopPoint->setPdfHash($ack->getPayload()->generationResult->pdfHash);
                    $pdfGenerationDate = new \DateTime();
                    $pdfGenerationDate->setTimestamp($ack->getPayload()->generationResult->created);
                    $stopPoint->setPdfGenerationDate($pdfGenerationDate);
                    $this->om->persist($stopPoint);
                    //TODO: call http://jira.canaltp.fr/browse/METH-202
                    $this->mediaManager->saveStopPointTimetable(
                        $timetable,
                        $stopPoint->getExternalId(),
                        $ack->getPayload()->generationResult->filepath
                    );
                } elseif (isset($ack->getPayload()->error)) {
                    throw new \Exception('Ack error msg: ' . $ack->getPayload()->error);
                }
            } catch (\Exception $e) {
                $task->fail();
                echo "ERROR during task Completion, task n°" . $task->getId() . "\n";
                echo $e->getMessage() . "\n";
            }
        }
        $task->complete();
    }

    // todo: remove generated _bak.pdf from mediamanager
    private function rollback($task)
    {
        echo "Rollback";
        foreach ($task->getAmqpAcks() as $ack) {
            if ($ack->getPayload()->generated == true) {
                $lineConfig = $this->getLineConfig($ack, $lineConfig);
                $timetable = $this->getTimetable($ack, $lineConfig, $timetable);
                $media = $this->mediaManager->getStopPointTimetableMedia($timetable, $ack->getPayload()->timetableParams->externalStopPointId);
                //TODO: mutualize this with workers when refactoring
                $media->setFileName(MediaManager::TIMETABLE_FILENAME . '_tmp.pdf');
                $media->delete();
            }
        }
    }

    private function completeDistributionList($task)
    {
        // save this list in db
        $timetable = $this->timetableRepo->find($task->getObjectId());
        $distributionList = $this->om->getRepository('CanalTPMttBundle:DistributionList')->findOneBy(
            array(
                'externalRouteId' => $timetable->getExternalRouteId(),
                'network' => $task->getNetwork()->getId()
            )
        );
        $this->om->refresh($distributionList);
        $paths = array();
        foreach ($distributionList->getIncludedStops() as $stopPointId) {
            $media = $this->mediaManager->getStopPointTimetableMedia($timetable, $stopPointId);
            $path = $this->mediaManager->getPathByMedia($media);
            if (!empty($path)) {
                $paths[] = $path;
            }
        }
        $pdfGenerator = $this->container->get('canal_tp_mtt.pdf_generator');
        $distributionListManager = $this->container->get('canal_tp.mtt.distribution_list_manager');
        $pdfGenerator->aggregatePdf($paths, $distributionListManager->generateAbsoluteDistributionListPdfPath($timetable));

        echo "Distribution List saved to ", $distributionListManager->generateAbsoluteDistributionListPdfPath($timetable), " / Files agregated ", count($paths), "\r\n";
    }

    private function completeSeasonPdfGen($task)
    {
        $season = $this->getSeason($task->getObjectId());
        $options = $task->getOptions();
        if (!empty($options)) {
            if (isset($options['publishSeasonOnComplete']) && !empty($options['publishSeasonOnComplete'])) {
                $season->setPublished(true);
                echo "Publish season " . $season->getTitle();
            }
        }
        echo "Unlock Season: " . $season->getTitle() . "\n";
        $season->setLocked(false);
        $this->om->persist($season);
    }

    public function completePdfGenTask($task)
    {
        echo "PdfGenCompletionLib:task n°" . $task->getId() . " completion started\n";
        $task->setCompletedAt(new \DateTime("now"));
        if ($task->isCanceled()) {
            $this->rollback($task);
        } else {
            $this->commit($task);
            switch ($task->getTypeId()) {
                case AmqpTask::DISTRIBUTION_LIST_PDF_GENERATION_TYPE:
                    $this->completeDistributionList($task);
                    break;
                case AmqpTask::SEASON_PDF_GENERATION_TYPE:
                    $this->completeSeasonPdfGen($task);
                    break;
            }
        }
        $this->om->flush();
        echo "PdfGenCompletionLib:task n°" . $task->getId() . " completion realized\n";
    }
}
