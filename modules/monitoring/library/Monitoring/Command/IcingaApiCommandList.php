<?php
/* Icinga Web 2 | (c) 2016 Icinga Development Team | GPLv2+ */

namespace Icinga\Module\Monitoring\Command;

use ArrayIterator;
use IteratorAggregate;
use Traversable;

/**
 * A list of IcingaApiCommands
 */
class IcingaApiCommandList implements IteratorAggregate
{
    /**
     * @var array
     */
    protected $commands = array();

    /**
     * IcingaApiCommandList constructor
     *
     * @param   array   $iterator
     */
    public function __construct(array $iterator)
    {
        foreach ($iterator as $command) {
            $this->commands[] = $command;
        }
    }

    /**
     * Create a new list with one Icinga 2 API command
     *
     * @param   string  $endpoint
     * @param   array   $data
     *
     * @return  IcingaApiCommandList
     */
    public static function create($endpoint, array $data)
    {
        return new static(array(IcingaApiCommand::create($endpoint, $data)));
    }

    /**
     * Retrieve an external iterator
     *
     * @return Traversable
     */
    public function getIterator()
    {
        return new ArrayIterator($this->commands);
    }
}
