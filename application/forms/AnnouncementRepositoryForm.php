<?php
/* Icinga Web 2 | (c) 2016 Icinga Development Team | GPLv2+ */

namespace Icinga\Forms;

use Icinga\Authentication\Auth;
use Icinga\Data\Filter\Filter;

/**
 * Create, update and delete announcements
 */
class AnnouncementRepositoryForm extends RepositoryForm
{
    /**
     * {@inheritDoc}
     */
    protected function createInsertElements(array $formData)
    {
        $this->addElement(
            'text',
            'author',
            array(
                'required'  => true,
                'value'     => Auth::getInstance()->getUser()->getUsername(),
                'disabled'  => true
            )
        );
        $this->addElement(
            'textarea',
            'message',
            array(
                'required'      => true,
                'label'         => $this->translate('Message'),
                'description'   => $this->translate('The message to display to users')
            )
        );
        $this->addElement(
            'dateTimePicker',
            'start',
            array(
                'required'      => true,
                'label'         => $this->translate('Start'),
                'description'   => $this->translate('The time to display the announcement from')
            )
        );
        $this->addElement(
            'dateTimePicker',
            'end',
            array(
                'required'      => true,
                'label'         => $this->translate('End'),
                'description'   => $this->translate('The time to display the announcement until')
            )
        );

        $this->setTitle($this->translate('Create a new announcement'));
        $this->setSubmitLabel($this->translate('Create'));
    }

    /**
     * {@inheritDoc}
     */
    protected function createUpdateElements(array $formData)
    {
        $this->createInsertElements($formData);
        $this->setTitle(sprintf($this->translate('Edit announcement %s'), $this->getIdentifier()));
        $this->setSubmitLabel($this->translate('Save'));
    }

    /**
     * {@inheritDoc}
     */
    protected function createDeleteElements(array $formData)
    {
        $this->setTitle(sprintf($this->translate('Remove announcement %s?'), $this->getIdentifier()));
        $this->setSubmitLabel($this->translate('Yes'));
    }

    /**
     * {@inheritDoc}
     */
    protected function createFilter()
    {
        return Filter::where('id', $this->getIdentifier());
    }

    /**
     * {@inheritDoc}
     */
    protected function getInsertMessage($success)
    {
        return $success
            ? $this->translate('Announcement created successfully')
            : $this->translate('Failed to create announcement');
    }

    /**
     * {@inheritDoc}
     */
    protected function getUpdateMessage($success)
    {
        return sprintf(
            $success
                ? $this->translate('Announcement %s has been edited')
                : $this->translate('Failed to edit announcement %s'),
            $this->getIdentifier()
        );
    }

    /**
     * {@inheritDoc}
     */
    protected function getDeleteMessage($success)
    {
        return sprintf(
            $success
                ? $this->translate('Announcement %s has been removed')
                : $this->translate('Failed to remove announcement %s'),
            $this->getIdentifier()
        );
    }
}
