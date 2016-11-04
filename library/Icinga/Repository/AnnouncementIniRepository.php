<?php
/* Icinga Web 2 | (c) 2016 Icinga Development Team | GPLv2+ */

namespace Icinga\Repository;

use DateTime;
use Icinga\Application\Config;
use Icinga\Web\Announcement;

/**
 * A collection of announcements stored in an INI file
 */
class AnnouncementIniRepository extends IniRepository
{
    /**
     * {@inheritDoc}
     */
    protected $queryColumns = array('announcement' => array('id', 'author', 'message', 'hash', 'start', 'end'));

    /**
     * {@inheritDoc}
     */
    protected $conversionRules = array('announcement' => array(
        'start' => 'timestamp',
        'end'   => 'timestamp'
    ));

    /**
     * {@inheritDoc}
     */
    protected $triggers = array('announcement');

    /**
     * {@inheritDoc}
     */
    protected $configs = array('announcement' => array(
        'name'      => 'announcements',
        'keyColumn' => 'id'
    ));

    /**
     * Create a DateTime from a *nix timestamp
     *
     * @param   string  $timestamp
     *
     * @return  DateTime|null
     */
    protected function retrieveTimestamp($timestamp)
    {
        return $timestamp === null ? null : DateTime::createFromFormat('U', $timestamp);
    }

    /**
     * Get a DateTime's *nix timestamp
     *
     * @param   DateTime    $datetime
     *
     * @return  int|null
     */
    protected function persistTimestamp(DateTime $datetime)
    {
        return $datetime === null ? null : $datetime->getTimestamp();
    }

    /**
     * Before-insert trigger (per row)
     *
     * @param   object  $new    The original data to insert
     *
     * @return  object          The eventually modified data to insert
     */
    protected function onInsertAnnouncement($new)
    {
        if (! isset($new->id)) {
            $new->id = uniqid('', true);
        }
        if (! isset($new->hash)) {
            $announcement = new Announcement((array) $new);
            $new->hash = $announcement->getHash();
        }

        return $new;
    }

    /**
     * Before-update trigger (per row)
     *
     * @param   object  $old    The original data as currently stored
     * @param   object  $new    The original data to update
     *
     * @return  object          The eventually modified data to update
     */
    protected function onUpdateAnnouncement($old, $new)
    {
        if ($new->message !== $old->message) {
            $announcement = new Announcement((array) $new);
            $new->hash = $announcement->getHash();
        }

        return $new;
    }
}
