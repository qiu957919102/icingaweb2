<?php
/* Icinga Web 2 | (c) 2016 Icinga Development Team | GPLv2+ */

namespace Icinga\Repository;

use DateTime;
use Icinga\Application\Config;
use Icinga\Data\Filter\Filter;
use Icinga\Web\Announce;

/**
 * A collection of Announces stored in an INI file
 */
class AnnounceIniRepository extends IniRepository
{
    /**
     * {@inheritDoc}
     */
    public function __construct($ds = null)
    {
        if ($ds === null) {
            $ds = Config::app('announces');
        }
        $config = $ds->getConfigObject();
        if ($config->getKeyColumn() === null) {
            $config->setKeyColumn('id');
        }
        parent::__construct($ds);
    }

    /**
     * {@inheritDoc}
     */
    protected $queryColumns = array('announce' => array('id', 'author', 'message', 'hash', 'start', 'end'));

    /**
     * {@inheritDoc}
     */
    protected $conversionRules = array('announce' => array(
        'start' => 'timestamp',
        'end'   => 'timestamp'
    ));

    /**
     * {@inheritDoc}
     */
    protected $triggers = array('announce');

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
    protected function onInsertAnnounce($new)
    {
        if (! isset($new->id)) {
            $new->id = uniqid('', true);
        }
        if (! isset($new->hash)) {
            $announce = new Announce($new);
            $new->hash = $announce->getHash();
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
    protected function onUpdateAnnounce($old, $new)
    {
        if ($new->message !== $old->message) {
            $options = (array) $new;
            unset($options['id']);
            unset($options['hash']);
            $announce = new Announce($options);
            $new->hash = $announce->getHash();
        }

        return $new;
    }
}
