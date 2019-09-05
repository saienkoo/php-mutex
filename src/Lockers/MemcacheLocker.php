<?php
/**
 * Created by PhpStorm.
 * User: alexander
 * Date: 18.09.18
 * Time: 12:30
 */

namespace Lamer1\Mutex\Lockers;

class MemcacheLocker extends ALockerAbstract
{
    const MAX_EXPIRATION = 300;

    /** @var Memcache|Memcached */
    private $_memcache = null;

    /** @var bool  */
    private $_useMemcached;

    /**
     * MemcacheLocker constructor.
     * @param $memcache
     */
    public function __construct($memcache)
    {
        $this->_memcache = $memcache;
        $this->_useMemcached = $memcache instanceof Memcached;

        return parent::__construct();
    }

    /**
     * @param string $name
     * @param null $expiration
     * @return bool
     */
    protected function lock($name, $expiration = null)
    {
        if ($this->_useMemcached) {
            $lock = $this->_memcache->add($name, $this->lockInformation, $expiration ?? $this->expiration);
        } else {
            $lock = $this->_memcache->add($name, $this->lockInformation, 0, $expiration ?? $this->expiration);
        }
        return $lock;
    }

    /**
     * @param string $name
     * @return bool
     */
    protected function unlock($name)
    {
        if (isset($this->locks[$name]) && $this->_memcache->delete($name)) {
            unset($this->locks[$name]);

            return true;
        }

        return false;
    }

    /**
     * @param string $name
     * @return bool
     */
    public function isLocked($name)
    {
        return false !== $this->_memcache->get($name);
    }
}
