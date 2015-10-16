<?php

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
    private $stopTimetableRepo = null;
    private $stopPointRepo = null;
    private $areaPdfRepo = null;

    public function __construct(ObjectManager $om, MediaManager $mediaManager, $container)
    {
        $this->om = $om;
        $this->mediaManager = $mediaManager;
        $this->container = $container;
        $this->lineConfigRepo = $this->om->getRepository('CanalTPMttBundle:LineConfig');
        $this->stopTimetableRepo = $this->om->getRepository('CanalTPMttBundle:StopTimetable');
        $this->stopPointRepo = $this->om->getRepository('CanalTPMttBundle:StopPoint');
        $this->areaPdfRepo = $this->om->getRepository('CanalTPMttBundle:AreaPdf');
    }

    private function getLineConfig($ack, $lineConfig)
    {
        if ($lineConfig == false ||
            // check if this ack is for a different lineConfig than the previous one
            $lineConfig->getExternalLineId() != $ack->getPayload()->stopTimetableParams->externalLineId
        ) {
            $lineConfig = $this->lineConfigRepo->findOneBy(
                array(
                    'externalLineId' => $ack->getPayload()->stopTimetableParams->externalLineId,
                    'season' => $ack->getPayload()->stopTimetableParams->seasonId
                )
            );
        }

        if (is_null($lineConfig)) {
            throw new \Exception('LineConfig not found, maybe layout is deleted.');
        }

        return $lineConfig;
    }

    private function getStopTimetable($ack, $lineConfig, $stopTimetable)
    {
        if ($stopTimetable == false ||
            // check if this ack is for a different stopTimetable than the previous one
            $stopTimetable->getExternalRouteId() != $ack->getPayload()->stopTimetableParams->externalRouteId
        ) {
            $stopTimetable = $this->stopTimetableRepo->findOneBy(
                array(
                    'externalRouteId' => $ack->getPayload()->stopTimetableParams->externalRouteId,
                    'line_config' => $lineConfig->getId()
                )
            );
        }

        return $stopTimetable;
    }

    private function getStopPoint($ack, $stopTimetable)
    {
        return $this->stopPointRepo->findOneBy(
            array(
                'stopTimetable' => $stopTimetable->getId(),
                'externalId' => $ack->getPayload()->stopTimetableParams->externalStopPointId
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
        $stopTimetable = false;
        foreach ($task->getAmqpAcks() as $ack) {
            try {
                if ($ack->getPayload()->generated == true) {
                    $lineConfig = $this->getLineConfig($ack, $lineConfig);
                    $stopTimetable = $this->getStopTimetable($ack, $lineConfig, $stopTimetable);
                    $stopPoint = $this->getStopPoint($ack, $stopTimetable);
                    if (empty($stopPoint)) {
                        $stopPoint = new StopPoint();
                        $stopPoint->setStopTimetable($stopTimetable);
                        $stopPoint->setExternalId($ack->getPayload()->stopTimetableParams->externalStopPointId);
                    }
                    $stopPoint->setPdfHash($ack->getPayload()->generationResult->pdfHash);
                    $pdfGenerationDate = new \DateTime();
                    $pdfGenerationDate->setTimestamp($ack->getPayload()->generationResult->created);
                    $stopPoint->setPdfGenerationDate($pdfGenerationDate);
                    $this->om->persist($stopPoint);
                    $this->mediaManager->saveStopPointStopTimetable(
                        $stopTimetable,
                        $stopPoint->getExternalId(),
                        $ack->getPayload()->generationResult->filepath
                    );
                    $this->removeTmpMedia($stopTimetable, $stopPoint->getExternalId());
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

    private function removeTmpMedia($stopTimetable, $externalStopPointId)
    {
        $media = $this->mediaManager->getStopPointStopTimetableMedia(
            $stopTimetable,
            $externalStopPointId
        );
        //TODO: mutualize this with workers when refactoring
        $media->setBaseName(MediaManager::TIMETABLE_FILENAME . '_tmp.pdf');
        echo $this->mediaManager->getPathByMedia($media) . "\r\n";
        $media->delete();
    }

    // Remove generated _tmp.pdf from mediamanager
    private function rollback($task)
    {
        echo "Rollback task n°" . $task->getId() . "\r\n";
        $lineConfig = false;
        $stopTimetable = false;
        foreach ($task->getAmqpAcks() as $ack) {
            if ($ack->getPayload()->generated == true) {
                $lineConfig = $this->getLineConfig($ack, $lineConfig);
                $stopTimetable = $this->getStopTimetable($ack, $lineConfig, $stopTimetable);
                $this->removeTmpMedia($stopTimetable, $ack->getPayload()->stopTimetableParams->externalStopPointId);
            }
        }
    }

    private function findAckByStop($amqpAcks, $stop)
    {
        foreach ($amqpAcks as $ack) {
            if ($ack->getPayload()->stopTimetableParams->externalStopPointId == $stop->stopPointId
                && $ack->getPayload()->stopTimetableParams->externalRouteId == $stop->routeId
                && $ack->getPayload()->stopTimetableParams->externalLineId == $stop->lineId
            ) {
                return $ack;
            }
        }

        return null;
    }

    private function completeAreaList($task)
    {
        $pdfGenerator = $this->container->get('canal_tp_mtt.pdf_generator');
        $areaPdfManager = $this->container->get('canal_tp_mtt.area_pdf_manager');
        $areaPdf = $this->areaPdfRepo->find($task->getObjectId());
        $this->om->refresh($areaPdf);

        $paths = array();
        $lineConfig = false;
        $stopTimetable = false;

        foreach ($areaPdf->getArea()->getStopPoints() as $stop) {
            $stopObj = json_decode($stop);
            $ack = $this->findAckByStop($task->getAmqpAcks(), $stopObj);

            if (is_null($ack)) {
                echo 'No ack for : ' . $stop;
                continue;
            }

            try {
                $lineConfig = $this->getLineConfig($ack, $lineConfig);
            } catch (\Exception $e) {
                echo 'No lineConfig for : ' . $stop;
                continue;
            }
            $stopTimetable = $this->getStopTimetable($ack, $lineConfig, $stopTimetable);

            $media = $this->mediaManager->getStopPointStopTimetableMedia($stopTimetable, $ack->getPayload()->stopTimetableParams->externalStopPointId);
            $path = $this->mediaManager->getPathByMedia($media);
            if (!empty($path)) {
                $paths[] = $path;
            }
        }
        $pdfGenerator->aggregatePdf(
            $paths,
            $areaPdfManager->generateAbsoluteAreaPdfPath($areaPdf)
        );
        $areaPdf->setGeneratedAt(new \DateTime());

        echo "Area saved to ", $areaPdfManager->generateAbsoluteAreaPdfPath($areaPdf), " / Files aggregated ", count($paths), "\r\n";
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
        echo "task status " . $task->getStatus() . "\n";
        if ($task->isCanceled()) {
            $this->rollback($task);
        } else {
            $this->commit($task);
            switch ($task->getTypeId()) {
                case AmqpTask::SEASON_PDF_GENERATION_TYPE:
                    $this->completeSeasonPdfGen($task);
                    break;
                case AmqpTask::AREA_PDF_GENERATION_TYPE:
                    $this->completeAreaList($task);
                    break;
            }
        }
        $task->setCompletedAt(new \DateTime("now"));
        $this->om->flush();
        echo "PdfGenCompletionLib:task n°" . $task->getId() . " completion realized\n";
    }
}
