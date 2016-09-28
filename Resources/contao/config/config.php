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
