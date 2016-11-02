<?php
/* Icinga Web 2 | (c) 2016 Icinga Development Team | GPLv2+ */

namespace Icinga\Controllers;

use Icinga\Exception\NotFoundError;
use Icinga\Forms\AnnouncementRepositoryForm;
use Icinga\Repository\AnnouncementIniRepository;
use Icinga\Web\Controller;
use Icinga\Web\Url;

class AnnouncementsController extends Controller
{
    /**
     * List all announcements
     */
    public function indexAction()
    {
        $this->assertPermission('admin/announcements');

        $repo = new AnnouncementIniRepository();
        $this->view->announcements = $query = $repo
            ->select(array('id', 'author', 'message', 'start', 'end'))
            ->order('start', 'ASC');

        $this->getTabs()->add(
            'announcements',
            array(
                'url'       => Url::fromPath('announcements'),
                'label'     => $this->translate('Announcements'),
                'title'     => $this->translate('List All Announcements'),
                'active'    => true
            )
        );
    }

    /**
     * Add an announcement
     */
    public function addAction()
    {
        $form = $this->prepareForm()->add();
        $form->handleRequest();
        $this->renderForm($form, $this->translate('New Announcement'));
    }

    /**
     * Edit an announcement
     */
    public function editAction()
    {
        $form = $this->prepareForm()->edit($this->params->getRequired('id'));
        try {
            $form->handleRequest();
        } catch (NotFoundError $_) {
            $this->httpNotFound($this->translate('Announcement not found'));
        }
        $this->renderForm($form, $this->translate('Update Announcement'));
    }

    /**
     * Remove an announcement
     */
    public function removeAction()
    {
        $form = $this->prepareForm()->remove($this->params->getRequired('id'));
        try {
            $form->handleRequest();
        } catch (NotFoundError $_) {
            $this->httpNotFound($this->translate('Announcement not found'));
        }
        $this->renderForm($form, $this->translate('Remove Announcement'));
    }

    /**
     * Assert permission admin/announcements and return a prepared RepositoryForm
     *
     * @return AnnouncementRepositoryForm
     */
    protected function prepareForm()
    {
        $this->assertPermission('admin/announcements');

        $form = new AnnouncementRepositoryForm();
        return $form
            ->setRepository(new AnnouncementIniRepository())
            ->setRedirectUrl(Url::fromPath('announcements'));
    }
}
