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
            $config->setKeyColumn('hash');
        }
        parent::__construct($ds);
    }

    /**
     * {@inheritDoc}
     */
    protected $queryColumns = array('announce' => array('hash', 'author', 'message', 'start', 'end'));

    /**
     * {@inheritDoc}
     */
    protected $conversionRules = array('announce' => array(
        'start' => 'timestamp',
        'end'   => 'timestamp'
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
     * {@inheritDoc}
     */
    public function insert($target, array $data)
    {
        if (! isset($data['hash'])) {
            $announce = new Announce($data);
            $data['hash'] = $announce->getHash();
        }
        parent::insert($target, $data);
    }

    /**
     * {@inheritDoc}
     */
    public function update($target, array $data, Filter $filter = null)
    {
        $query = $this->select(array('hash', 'author', 'message', 'start', 'end'));
        if ($filter !== null) {
            $query->applyFilter($filter);
        }
        foreach ($query->fetchAll() as $row) {
            $row = (array) $row;
            $hash = $row['hash'];
            unset($row['hash']);
            $announce = new Announce(array_merge($row, $data));
            $data['hash'] = $announce->getHash();
            parent::update($target, $data, Filter::expression('hash', '=', $hash));
            unset($data['hash']);
        }
    }
}
