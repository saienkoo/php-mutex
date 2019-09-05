<?php
/**
 * Created by PhpStorm.
 * User: alexander
 * Date: 18.09.18
 * Time: 15:58
 */

namespace Lamer1\Mutex\Lockers;

class FileLocker extends ALockerAbstract
{
    /** @var int  */
    const MAX_EXPIRATION = 86400;

    /** @var string|null */
    private $_dir = null;

    /**
     * FileLocker constructor.
     * @param string $tempDir
     */
    public function __construct($tempDir)
    {
        $this->_dir = $tempDir;
        return parent::__construct();
    }

    /**
     * @param string $name
     * @param null|int $expiration
     * @return mixed
     */
    protected function lock($name, $expiration = null)
    {
        if ($this->isLocked($name)) {
            return false;
        }
        $fileName = $name . '.lock';
        return file_put_contents(
            $this->getDir() . $fileName,
            serialize(array_merge($this->lockInformation, ['expiration' => time() + $expiration !== null ? $expiration : $this->expiration])),
                LOCK_EX
            ) !== false;
    }

    /**
     * @param string $name
     * @return mixed
     */
    protected function unlock($name)
    {
        $fileName = $name . '.lock';
        if (isset($this->locks[$name])) {
            unset($this->locks[$name]);
            file_put_contents($this->getDir() . $fileName, serialize([]), LOCK_EX);
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
        $name = $name . '.lock';
        if (!file_exists($this->getDir() . $name)) {
            return false;
        }
        $fileContents = @unserialize(file_get_contents($this->getDir() . $name));
        if ($fileContents) {
            $time = $fileContents['expiration'] ?? null;
            if (time() <= $time) {
                return true;
            }
        }
        file_put_contents($this->getDir() . $name, serialize([]), LOCK_EX);
        return false;
    }

    /**
     * @return string
     */
    protected function getDir()
    {
        return $this->_dir;
    }
}
