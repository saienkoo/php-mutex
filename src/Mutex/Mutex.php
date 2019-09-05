<?php
/**
 * Created by PhpStorm.
 * User: alexander
 * Date: 18.09.18
 * Time: 12:12
 */

namespace Lamer1\Mutex\Mutex;

use Lamer1\Mutex\Interfaces\ILockerInterface;

class Mutex
{
    /** @var string  */
    protected $name;

    /** @var ILockerInterface  */
    protected $locker;

    /**
     * UMutex constructor.
     * @param string $name
     * @param ILockerInterface $locker
     */
    public function __construct($name, ILockerInterface $locker)
    {
        $this->name = $name;
        $this->locker = $locker;
    }

    /**
     * @param null|int $timeout
     * @param null|int $expiration
     * @return bool
     */
    public function acquireLock($timeout = null, $expiration = null)
    {
        return $this->locker->acquireLock($this->name, $timeout, $expiration);
    }

    /**
     * @return bool
     */
    public function releaseLock()
    {
        return $this->locker->releaseLock($this->name);
    }

    /**
     * @return bool
     */
    public function isLocked()
    {
        return $this->locker->isLocked($this->name);
    }
}
