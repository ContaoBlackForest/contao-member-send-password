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
