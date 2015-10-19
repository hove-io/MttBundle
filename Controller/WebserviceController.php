<?php

namespace CanalTP\MttBundle\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;

use CanalTP\MediaManagerBundle\Entity\Media;

class WebserviceController extends AbstractController
{
    private function getMediaUrl($externalNetworkId, $externalRouteId, $externalStopPointId, $seasonId)
    {
        $mediaManager = $this->get('canal_tp_mtt.media_manager');
        $seasonCategory = $mediaManager->getSeasonCategory(
            $externalNetworkId,
            $externalRouteId,
            $seasonId,
            $externalStopPointId
        );

        $media = new Media();
        $media->setCategory($seasonCategory);
        $media->setFileName($mediaManager::TIMETABLE_FILENAME);

        return $mediaManager->getUrlByMedia($media);
    }

    private function findSeasonByCustomerAndDate($identifier, $externalNetworkId, \DateTime $date)
    {
        $customer = $this->get('sam_core.customer')->findOneBy(array('identifier' => $identifier));
        $seasonManager = $this->get('canal_tp_mtt.season_manager');

        if ($customer == null) {
            throw new \Exception($this->get('translator')->trans('webservice.no_customer_found', array('%customer%' => $customerNameCanonical), 'exceptions'), 404);
        }
        $season = $seasonManager->findSeasonByPerimeterAndDateTime(
            $this->get('canal_tp_mtt.perimeter_manager')->findOneByExternalNetworkId(
                $customer,
                $externalNetworkId
            ),
            $date
        );

        return ($season);
    }

    #TODO: Remove this route when divia.fr updated (Refs #METH-420)
    public function getTimetableUrlOldAction($externalNetworkId, $externalRouteId, $externalStopPointId)
    {
        return ($this->getTimetableUrlAction('divia', $externalNetworkId, $externalRouteId, $externalStopPointId));
    }

    public function getTimetableUrlAction($customerNameCanonical, $externalNetworkId, $externalRouteId, $externalStopPointId)
    {
        try {
            $filter = $this->getRequest()->query->get('filter');
            if (empty($filter)) {
                // default value is now
                $date = new \DateTime("now");
            } else {
                $date = new \DateTime($filter);
            }
            $season = $this->findSeasonByCustomerAndDate($customerNameCanonical, $externalNetworkId, $date);
            if (empty($season)) {
                throw new \Exception($this->get('translator')->trans(
                    'webservice.no_season_found',
                    array('%date%' => $date->format('d/m/Y')),
                    'exceptions'
                ),
                404);
            }
            $mediaUrl = $this->getMediaUrl($externalNetworkId, $externalRouteId, $externalStopPointId, $season[0]['id']);
            if (empty($mediaUrl)) {
                throw new \Exception($this->get('translator')->trans('webservice.no_timetable_found', array('%date%' => $date->format('d/m/Y')), 'exceptions'), 404);
            }

            return $this->redirect($mediaUrl);

        } catch (\Exception $e) {
            $code = in_array($e->getCode(), array(404, 500)) ? $e->getCode() : 500;
            $response = new JsonResponse();
            $response->setData(array(
                'message' => $e->getMessage(),
                'error_code' => $code,
            ));
            $response->setStatusCode($code);

            return $response;
        }
    }
}
