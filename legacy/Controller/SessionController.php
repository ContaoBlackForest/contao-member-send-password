<?php

/**
 * This file is part of contao-member-send-password.
 *
 * (c) 2016-2019 The Contao Blackforest team.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    contaoblackforest/contao-member-send-password
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @copyright  20116-2019 The Contao Blackforest team.
 * @license    https://github.com/contaoblackforest/contao-member-send-password/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
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
