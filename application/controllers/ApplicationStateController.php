<?php
/* Icinga Web 2 | (c) 2015 Icinga Development Team | GPLv2+ */

namespace Icinga\Controllers;

use DateTime;
use Icinga\Application\Icinga;
use Icinga\Data\Filter\Filter;
use Icinga\Repository\AnnouncementIniRepository;
use Icinga\Web\Controller;
use Icinga\Web\Session;

/**
 * @TODO(el): https://dev.icinga.org/issues/10646
 */
class ApplicationStateController extends Controller
{
    public function indexAction()
    {
        $this->cleanUpAnnouncements();

        if (isset($_COOKIE['icingaweb2-session'])) {
            $last = (int) $_COOKIE['icingaweb2-session'];
        } else {
            $last = 0;
        }
        $now = time();
        if ($last + 600 < $now) {
            Session::getSession()->write();
            $params = session_get_cookie_params();
            setcookie(
                'icingaweb2-session',
                $now,
                null,
                $params['path'],
                $params['domain'],
                $params['secure'],
                $params['httponly']
            );
            $_COOKIE['icingaweb2-session'] = $now;
        }
        Icinga::app()->getResponse()->setHeader('X-Icinga-Container', 'ignore', true);
    }

    /**
     * Delete all expired announcements
     *
     * @return $this
     */
    protected function cleanUpAnnouncements()
    {
        // Clean up announcements as frequent as sessions
        $gcDivisor = ini_get('session.gc_divisor');
        if ($gcDivisor >= 1 && mt_rand(1, $gcDivisor) <= ini_get('session.gc_probability')) {
            $repo = new AnnouncementIniRepository();
            $repo->delete('announcement', Filter::expression('end', '<', new DateTime()));
        }

        return $this;
    }
}
