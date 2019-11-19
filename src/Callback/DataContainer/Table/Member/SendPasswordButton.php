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

declare(strict_types=1);

namespace ContaoBlackForest\MemberSendPasswordBundle\Callback\DataContainer\Table\Member;

use Contao\Config;
use Contao\CoreBundle\Framework\Adapter;
use Twig\Environment;

/**
 * This class inject the send password button by select mode.
 */
final class SendPasswordButton
{
    /**
     * The config.
     *
     * @var Adapter|Config
     */
    private $config;

    /**
     * The twig environment.
     *
     * @var Environment
     */
    private $twig;

    /**
     * The constructor.
     *
     * @param Adapter     $config The config.
     * @param Environment $twig   The twig environment.
     */
    public function __construct(Adapter $config, Environment $twig)
    {
        $this->config = $config;
        $this->twig   = $twig;
    }

    /**
     * Inject the send password button by select mode.
     *
     * @param array $buttons The buttons.
     *
     * @return array
     */
    public function __invoke(array $buttons): array
    {
        if ($GLOBALS['TL_DCA']['tl_member']['config']['notEditable']) {
            return $buttons;
        }

        $buttons['password'] = $this->twig->render(
            '@BlackForestMemberSendPassword/Backend/be_button.html.twig',
            [
                'name'         => 'password',
                'id'           => 'password',
                'accesskey'    => 'p',
                'label'        => 'MSC.passwordSelected',
                'trans_domain' => 'contao_default',
                'charset'      => $this->config->get('characterSet')
            ]
        );

        return $buttons;
    }
}
