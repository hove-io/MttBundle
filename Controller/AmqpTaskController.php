<?php

namespace CanalTP\MttBundle\Controller;

class AmqpTaskController extends AbstractController
{
    public function cancelAction($externalNetworkId, $taskId)
    {
        try {
            $this->get('canal_tp_mtt.task_cancelation')->cancel($taskId);
        } catch (\Exception $e) {
            $this->get('session')->getFlashBag()->add(
                'danger',
                $this->get('translator')->trans(
                    $e->getMessage(),
                    array(),
                    'exceptions'
                )
            );
        }

        return $this->redirect(
            $this->generateUrl(
                'canal_tp_mtt_homepage',
                array(
                    'externalNetworkId' => $externalNetworkId,
                )
            )
        );
    }
}
