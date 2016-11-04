<?php
/* Icinga Web 2 | (c) 2016 Icinga Development Team | GPLv2+ */

namespace Icinga\Web;

use DateTime;
use Icinga\Data\Filter\Filter;
use Icinga\Data\Filter\FilterAnd;
use Icinga\Exception\ProgrammingError;
use Icinga\Repository\AnnouncementIniRepository;

/**
 * Load announcements for being displayed
 */
class AnnouncementsLoader
{
    /**
     * Singleton instance
     *
     * @var AnnouncementsLoader
     */
    private static $instance;

    /**
     * The loaded announcements
     *
     * @var Announcement[]
     */
    private $announcements;

    /**
     * Get singleton instance
     *
     * @return AnnouncementsLoader
     */
    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * AnnouncementsLoader constructor
     */
    final private function __construct()
    {
    }

    /**
     * Prevent an instance from being cloned
     */
    final private function __clone()
    {
        throw new ProgrammingError('Can not clone a singleton');
    }

    /**
     * Load and get announcements
     *
     * @return Announcement[]
     */
    public function getAnnouncements()
    {
        if ($this->announcements === null) {
            $repo = new AnnouncementIniRepository();
            $now = new DateTime();
            $query = $repo->select()
                ->setFilter(new FilterAnd(array(
                    Filter::expression('start', '<=', $now),
                    Filter::expression('end', '>=', $now)
                )))
                ->order('start');

            $announcements = array();
            foreach ($query as $announcement) {
                $announcements[] = new Announcement((array) $announcement);
            }
            $this->announcements = $announcements;
        }

        return $this->announcements;
    }
}
