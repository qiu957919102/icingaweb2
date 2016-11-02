<?php
/* Icinga Web 2 | (c) 2016 Icinga Development Team | GPLv2+ */

namespace Icinga\Application\Hook;

use Icinga\Exception\ProgrammingError;
use Icinga\Web\Announcement;

/**
 * Base class for announcement hooks
 *
 * Extend this class if you want your module to provide announcements.
 */
abstract class AnnouncementHook
{
    /**
     * Cache for {@link getAnnouncements()}
     *
     * @var Announcement[]
     */
    private $announcements = null;

    /**
     * AnnouncementHook constructor
     *
     * @see {@link init()} for hook initialization.
     */
    final public function __construct()
    {
        $this->init();
    }

    /**
     * Overwrite this function for hook initialization, e.g. loading the hook's config
     */
    protected function init()
    {
    }

    /**
     * Get all new announcements
     *
     * @return Announcement[]
     */
    abstract protected function loadAnnouncements();

    /**
     * Call {@link loadAnnouncements()} and validate, cache and return its return value
     *
     * @return  Announcement[]
     *
     * @throws  ProgrammingError    If {@link loadAnnouncements()} returns something invalid
     */
    final public function getAnnouncements()
    {
        if ($this->announcements === null) {
            $announcements = $this->loadAnnouncements();
            if (! is_array($announcements)) {
                throw new ProgrammingError(
                    '%s::loadAnnouncements() returned a %s, expected Icinga\Web\Announcement[]',
                    get_class($this),
                    is_object($announcements) ? get_class($announcements) : gettype($announcements)
                );
            }
            foreach ($announcements as $announcement) {
                if (! ($announcement instanceof Announcement)) {
                    throw new ProgrammingError(
                        '%s::loadAnnouncements() returned an array with a %s, expected only Icinga\Web\Announcement',
                        get_class($this),
                        is_object($announcement) ? get_class($announcement) : gettype($announcement)
                    );
                }
            }
            $this->announcements = $announcements;
        }

        return $this->announcements;
    }
}
