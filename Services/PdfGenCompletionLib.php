<?php

/**
 * Symfony service to call the pdfGenerator webservice
 *
 * @author vdegroote
 */
namespace CanalTP\MttBundle\Services;

use Doctrine\Common\Persistence\ObjectManager;
use CanalTP\MttBundle\Entity\StopPoint;

class PdfGenCompletionLib
{
    private $om = null;
    private $mediaManager = null;
    private $lineConfigRepo = null;
    private $timetableRepo = null;
    private $stopPointRepo = null;

    public function __construct(ObjectManager $om, MediaManager $mediaManager)
    {
        $this->om = $om;
        $this->mediaManager = $mediaManager;
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
        return $this->om->getRepository('CanalTPMttBundle:Season')->find($seasonId);
    }
    
    private function commit($task)
    {
        $lineConfig = false;
        $timetable = false;
        try {
            foreach ($task->getAmqpAcks() as $ack) {
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
                }
            }
            $options = $task->getOptions();
            
            if (!empty($options)) {
                if (isset($options['publishSeasonOnComplete']) && !empty($options['publishSeasonOnComplete'])) {
                    $season = $this->getSeason($task->getObjectId());
                    $season->setPublished(true);
                    echo "Publish season " . $season->getTitle();
                }
            }
            $task->complete();
        } catch (\Exception $e){
            echo "ERROR during task Completion, task n°" . $task->getId() . "\n";
            echo $e->getMessage() . "\n";
        }
    }
    
    // todo: remove generated _bak.pdf from mediamanager
    private function rollback($task)
    {
        echo "Rollback";
    }
    
    public function completePdfGenTask($task)
    {
        echo "PdfGenCompletionLib:task n°" . $task->getId() . " completion started\n";
        $task->setCompletedAt(new \DateTime("now"));
        if ($task->isCanceled()) {
            $this->rollback($task);
        } else {
            $this->commit($task);
        }
        $season = $this->getSeason($task->getObjectId());
        echo "Unlock Season: " . $season->getTitle() . "\n";
        $season->setLocked(false);
        $this->om->persist($season);
        $this->om->flush();
        echo "PdfGenCompletionLib:task n°" . $task->getId() . " completion realized\n";
    }
}