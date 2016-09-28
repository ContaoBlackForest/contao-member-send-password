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

namespace ContaoBlackForest\Member\SendPassword\DataContainer\Table;

/**
 * The data container class for member.
 */
class Member
{
    /**
     * Inject the send password button by select mode.
     *
     * @param array         $buttons
     *
     *
     * @return array
     */
    public function injectPasswordButton(array $buttons)
    {
        if ($GLOBALS['TL_DCA']['tl_member']['config']['notEditable']) {
            return $buttons;
        }

        $buttons['password'] =
            '<input type="submit" name="password" id="password" class="tl_submit" accesskey="p" value="' .
            specialchars($GLOBALS['TL_LANG']['MSC']['passwordSelected']) .
            '" onclick="if(!confirm(\'' . specialchars($GLOBALS['TL_LANG']['MSC']['passwordConfirm']) . '\'))' .
            'return false; Backend.getScrollOffset()">';

        return $buttons;
    }
}
