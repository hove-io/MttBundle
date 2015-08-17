<?php

namespace CanalTP\MttBundle\Tests\Functional\Controller;

use CanalTP\MttBundle\Tests\DataFixtures\ORM\Fixture;

class LayoutModelControllerTest extends AbstractControllerTest
{

    private function getRoute($route)
    {
        return $this->generateRoute(
            $route,
            array(
                'externalNetworkId' => Fixture::EXTERNAL_NETWORK_ID
            )
        );
    }

    private function getEditForm()
    {
        // Check if the form is correctly display
        $route = $this->getRoute('canal_tp_mtt_model_upload');
        $crawler = $this->doRequestRoute($route);

        // Submit form
        $form = $crawler->selectButton('Téléverser')->form();

        return $form;
    }

    public function testListModels()
    {
        $route = $this->getRoute('canal_tp_mtt_model_list');
        $crawler = $this->doRequestRoute($route);

        $this->assertGreaterThan(0, $crawler->filter('html:contains("Gestion des modèles")')->count());
        $this->assertGreaterThan(0, $crawler->filter('html:contains("Template par défaut")')->count());
        $this->assertEquals(1, $crawler->selectLink('Mettre à jour')->count());
        $this->assertEquals(1, $crawler->selectLink('Ajouter un modèle')->count());
    }

    public function testUploadGoodArchive()
    {
        $form = $this->getEditForm();
        $archive = new \Symfony\Component\HttpFoundation\File\UploadedFile(
            'vendor/canaltp/mtt-bundle/Tests/DataFixtures/ORM/layout1.zip',
            'layout1.zip'
        );
        $form['layout_config[file]'] = $archive;

        $this->client->followRedirects();
        $crawler = $this->client->submit($form);

        // Check if the value is saved correctly
        $this->assertGreaterThan(0, $crawler->filter('html:contains("Modèle enregistré")')->count());
    }
}
