<?php

/**
 * Copyright © ContaoBlackForest
 *
 * @package   contao-member-send-password
 * @author    Sven Baumann <baumann.sv@gmail.com>
 * @author    Dominik Tomasi <dominik.tomasi@gmail.com>
 * @license   GNU/LGPL
 * @copyright Copyright 2014-2016 ContaoBlackForest
 */

namespace ContaoBlackForest\Member\SendPassword\Controller;

use Contao\Input;

/**
 * The session controller.
 */
class SessionController
{
    const NAME = 'send-password';

    public function initialize()
    {
        if (TL_MODE !== 'BE'
            || Input::get('do') !== 'member'
            || Input::get('act') !== 'select'
            || !Input::post('password')
        ) {
            return;
        }

        $this->setCurrentIds();
    }

    /**
     * Return the current ids information.
     *
     * @return bool|array The current ids information.
     */
    public function getCurrentIds()
    {
        $data = $this->getSession();
        if (!$data
            || !array_key_exists('currentIds', $data)
        ) {
            return null;
        }

        return $data['currentIds'];
    }

    /**
     * Set current ids.
     *
     * @return void
     */
    protected function setCurrentIds()
    {
        $currentIds = Input::post('IDS');
        if (!$currentIds) {
            return;
        }

        $data = (array) $this->getSession();

        $data['currentIds'] = $currentIds;

        $this->setSession($data);
    }

    /**
     * Set data to the session.
     *
     * @param array $data The data.
     */
    protected function setSession(array $data)
    {
        $_SESSION[self::NAME] = $data;
    }

    /**
     * Return the session.
     *
     * @return array The session.
     */
    protected function getSession()
    {
        return $_SESSION[self::NAME];
    }

    /**
     * Remove the session.
     *
     * @return void
     */
    public function removeSession()
    {
        unset($_SESSION[self::NAME]);
    }

    /**
     * Set processed member.
     *
     * @param integer $value The member id.
     *
     * @return void
     */
    public function setProcessedMember($value)
    {
        $data = (array) $this->getSession();

        $data['processed'][] = $value;

        $this->setSession($data);
    }

    /**
     * Get the processed members.
     *
     * @return array The processed.
     */
    public function getProcessedMembers()
    {
        $data = (array) $this->getSession();

        if (!array_key_exists('processed', $data)) {
            return null;
        }

        return $data['processed'];
    }
}
