<?php

namespace CanalTP\MttBundle\Controller;

class AmqpTaskController extends AbstractController
{
    public function cancelAction($externalNetworkId, $taskId)
    {
        $this->get('canal_tp_mtt.task_cancelation')->cancel($taskId);
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