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

/** @see tl_member */

/**
 * Add select button.
 */
$GLOBALS['TL_DCA'][$this->strTable]['select']['buttons_callback'][] =
    array('ContaoBlackForest\Member\SendPassword\DataContainer\Table\Member', 'injectPasswordButton');
