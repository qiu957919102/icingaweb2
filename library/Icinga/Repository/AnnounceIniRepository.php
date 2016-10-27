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
        if (! isset($data['id'])) {
            $data['id'] = uniqid('', true);
        }
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
        if (isset($data['message'])) {
            $query = $this->select(array('id', 'author', 'message', 'start', 'end'));
            if ($filter !== null) {
                $query->applyFilter($filter);
            }
            foreach ($query->fetchAll() as $row) {
                $row = (array) $row;
                $id = $row['id'];
                unset($row['id']);
                $announce = new Announce(array_merge($row, $data));
                $data['hash'] = $announce->getHash();
                parent::update($target, $data, Filter::where('id', $id));
                unset($data['hash']);
            }
        } else {
            parent::update($target, $data, $filter);
        }
    }
}
