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
/**
 * Initialize session send member password.
 */
$GLOBALS['TL_HOOKS']['initializeSystem'][] =
    array('ContaoBlackForest\Member\SendPassword\Controller\SessionController', 'initialize');

/**
 * Parse template for send member password.
 */
$GLOBALS['TL_HOOKS']['parseTemplate'][] =
    array('ContaoBlackForest\Member\SendPassword\Controller\SendPasswordController', 'parseTemplate');
