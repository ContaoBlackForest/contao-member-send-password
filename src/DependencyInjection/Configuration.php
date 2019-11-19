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

namespace ContaoBlackForest\MemberSendPasswordBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * The extension configuration.
 */
final class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $builder = new TreeBuilder('cb_member_send_password');

        $node = $builder->root('cb_member_send_password');
        $node
            ->children()
                ->scalarNode('sessionKey')
                    ->cannotBeEmpty()
                    ->defaultValue('cb_member_send_password_session')
                ->end()
                ->scalarNode('sendCount')
                    ->cannotBeEmpty()
                    ->defaultValue(50)
                ->end()
                ->scalarNode('sendDelay')
                    ->cannotBeEmpty()
                    ->defaultValue(20)
                ->end()
            ->end();

        return $builder;
    }
}
