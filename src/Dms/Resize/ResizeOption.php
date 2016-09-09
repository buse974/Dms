<?php
/**
 * github.com/buse974/Dms (https://github.com/buse974/Dms).
 *
 * ResizeOption.php
 */
namespace Dms\Resize;

use Zend\Stdlib\AbstractOptions;

/**
 * Class Option Resize.
 */
class ResizeOption extends AbstractOptions
{
    /**
     * Size Allow.
     *
     * @var array
     */
    private $allow;

    /**
     * Check Size Allow.
     *
     * @var bool
     */
    private $active;

    /**
     * Set Option Size allow.
     *
     * @param array $allow
     *
     * @return \Dms\Resize\ResizeOption
     */
    public function setAllow($allow)
    {
        $this->allow = $allow;

        return $this;
    }

    /**
     * Get Option Size Allow.
     *
     * @return array
     */
    public function getAllow()
    {
        if (!$this->allow) {
            $this->allow = [];
        }

        return $this->allow;
    }

    /**
     * Set Option Active.
     *
     * @param bool $active
     *
     * @return \Dms\Resize\ResizeOption
     */
    public function setActive($active)
    {
        $this->active = $active;

        return $this;
    }

    /**
     * Get Option Active.
     *
     * @return bool
     */
    public function getActive()
    {
        return $this->active;
    }
}
