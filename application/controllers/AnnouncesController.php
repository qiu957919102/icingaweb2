<?php
/* Icinga Web 2 | (c) 2016 Icinga Development Team | GPLv2+ */

namespace Icinga\Controllers;

use Icinga\Exception\NotFoundError;
use Icinga\Forms\AnnounceRepositoryForm;
use Icinga\Repository\AnnounceIniRepository;
use Icinga\Web\Controller;
use Icinga\Web\Url;

class AnnouncesController extends Controller
{
    /**
     * List all announces
     */
    public function indexAction()
    {
        $this->assertPermission('admin/announces');

        $repo = new AnnounceIniRepository();
        $this->view->announces = $query = $repo
            ->select(array('id', 'author', 'message', 'start', 'end'))
            ->order('start', 'ASC');

        $this->getTabs()->add(
            'announces',
            array(
                'url'       => Url::fromPath('announces'),
                'label'     => $this->translate('Announces'),
                'title'     => $this->translate('List All Announces'),
                'active'    => true
            )
        );
    }

    /**
     * Add an announce
     */
    public function addAction()
    {
        $form = $this->prepareForm()->add();
        $form->handleRequest();
        $this->renderForm($form, $this->translate('New Announce'));
    }

    /**
     * Edit an announce
     */
    public function editAction()
    {
        $form = $this->prepareForm()->edit($this->params->getRequired('id'));
        try {
            $form->handleRequest();
        } catch (NotFoundError $_) {
            $this->httpNotFound($this->translate('Announce not found'));
        }
        $this->renderForm($form, $this->translate('Update Announce'));
    }

    /**
     * Remove an announce
     */
    public function removeAction()
    {
        $form = $this->prepareForm()->remove($this->params->getRequired('id'));
        try {
            $form->handleRequest();
        } catch (NotFoundError $_) {
            $this->httpNotFound($this->translate('Announce not found'));
        }
        $this->renderForm($form, $this->translate('Remove Announce'));
    }

    /**
     * Assert permission admin/announces and return a prepared RepositoryForm
     *
     * @return AnnounceRepositoryForm
     */
    protected function prepareForm()
    {
        $this->assertPermission('admin/announces');

        $form = new AnnounceRepositoryForm();
        return $form
            ->setRepository(new AnnounceIniRepository())
            ->setRedirectUrl(Url::fromPath('announces'));
    }
}
