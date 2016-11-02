<?php
/* Icinga Web 2 | (c) 2016 Icinga Development Team | GPLv2+ */

namespace Icinga\Application\Hook;

use Icinga\Exception\ProgrammingError;
use Icinga\Web\Announce;

/**
 * Base class for Announce hooks
 *
 * Extend this class if you want your module to provide Announces.
 */
abstract class AnnounceHook
{
    /**
     * Cache for {@link getAnnounces()}
     *
     * @var Announce[]
     */
    private $announces = null;

    /**
     * AnnounceHook constructor
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
     * Get all new Announces
     *
     * @return Announce[]
     */
    abstract protected function loadAnnounces();

    /**
     * Call {@link loadAnnounces()} and validate, cache and return its return value
     *
     * @return  Announce[]
     *
     * @throws  ProgrammingError    If {@link loadAnnounces()} returns something invalid
     */
    final public function getAnnounces()
    {
        if ($this->announces === null) {
            $announces = $this->loadAnnounces();
            if (! is_array($announces)) {
                throw new ProgrammingError(
                    '%s::loadAnnounces() returned a %s, expected Icinga\Web\Announce[]',
                    get_class($this),
                    is_object($announces) ? get_class($announces) : gettype($announces)
                );
            }
            foreach ($announces as $announce) {
                if (! ($announce instanceof Announce)) {
                    throw new ProgrammingError(
                        '%s::loadAnnounces() returned an array with a %s, expected only Icinga\Web\Announce',
                        get_class($this),
                        is_object($announce) ? get_class($announce) : gettype($announce)
                    );
                }
            }
            $this->announces = $announces;
        }

        return $this->announces;
    }
}
