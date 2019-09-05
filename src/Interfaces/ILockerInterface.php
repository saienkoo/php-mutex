<?php
/**
 * Created by PhpStorm.
 * User: alexander
 * Date: 18.09.18
 * Time: 12:29
 */

namespace Lamer1\Mutex\Interfaces;

interface ILockerInterface
{
    /**
     * @param string $name
     * @param null|int $timeout in milliseconds - time, which thread agrees to wait in query for lock
     * @param null|int $expiration in seconds - expiration time for value in storage
     * @return bool
     */
    public function acquireLock($name, $timeout = null, $expiration = null);

    /**
     * @param string $name
     * @return bool
     */
    public function releaseLock($name);

    /**
     * @param string $name
     * @return bool
     */
    public function isLocked($name);
}
