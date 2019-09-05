<?php
/**
 * Created by PhpStorm.
 * User: alexander
 * Date: 18.09.18
 * Time: 12:13
 */

namespace Lamer1\Mutex\Lockers;

use Lamer1\Mutex\Interfaces\ILockerInterface;
use Lamer1\Mutex\Mutex\Mutex;

abstract class ALockerAbstract implements ILockerInterface
{
    const USLEEP_TIME = 100;
    const MAX_EXPIRATION = 2592000;

    /** @var array  */
    protected $locks = [];

    /** @var array|null  */
    protected $lockInformation = null;

    /** @var int  */
    protected $expiration = 0;

    public function __construct()
    {
        $this->lockInformation = $this->generateLockInformation();
    }

    /**
     * @param int $expiration
     */
    public function setExpiration($expiration)
    {
        if ($expiration > static::MAX_EXPIRATION) {
            $expiration = static::MAX_EXPIRATION;
        }
        $this->expiration = $expiration;
    }

    /**
     * @param string $name
     * @param null|int $expiration
     * @return mixed
     */
    abstract protected function lock($name, $expiration = null);

    /**
     * @param string $name
     * @return mixed
     */
    abstract protected function unlock($name);

    /**
     * @param string $name
     * @param null|int $timeout
     * @param null|int $expiration
     * @return bool
     */
    public function acquireLock($name, $timeout = null, $expiration = null)
    {
        $blocking = $timeout === null;
        $instant = $timeout === 0;
        $start = microtime(true);
        $end = $start + $timeout / 1000;
        $locked = false;

        while ($blocking || $instant || microtime(true) < $end) {
            $instant = false;
            if (empty($this->locks[$name]) && $locked = $this->lock($name, $expiration)) {
                break;
            }
            usleep(static::USLEEP_TIME);
        }

        if ($locked) {
            $this->locks[$name] = true;

            return true;
        }

        return false;
    }

    /**
     * @param string $name
     * @return bool
     */
    public function releaseLock($name)
    {
        return $this->unlock($name);
    }

    /**
     * @return array
     */
    protected function generateLockInformation()
    {
        $pid = getmypid();
        $hostname = gethostname();
        $host = gethostbyname($hostname);

        // Compose data to one string
        $params = array();
        $params[] = $pid;
        $params[] = $host;
        $params[] = $hostname;

        return $params;
    }

    /**
     *
     */
    public function __destruct()
    {
        $errors = [];
        foreach ($this->locks as $name => $v) {
            $released = $this->unlock($name);
            if (!$released) {
                $errors[] = $name;
            }
        }
        if ($errors) {
            throw new \RuntimeException(sprintf('Cannot release lock in %s: %s', __METHOD__, implode(',', $errors)));
        }
    }

    /**
     * @param string $name
     * @return Mutex
     */
    public function getMutex($name)
    {
        return new Mutex($name, $this);
    }
}
