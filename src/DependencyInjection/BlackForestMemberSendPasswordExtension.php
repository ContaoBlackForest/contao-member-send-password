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

namespace ContaoBlackForest\MemberSendPasswordBundle\DependencyInjection;

use ContaoBlackForest\MemberSendPasswordBundle\Hook\DataContainer\Table\Member\RedirectSelectNotification;
use ContaoBlackForest\MemberSendPasswordBundle\View\Backend\AbstractSendPassword;
use ContaoBlackForest\MemberSendPasswordBundle\View\Backend\SendPasswordSendNotification;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

/**
 * The extension.
 */
final class BlackForestMemberSendPasswordExtension extends Extension
{
    /**
     * {@inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $container): void
    {
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));

        $loader->load('hooks.yml');
        $loader->load('services.yml');

        $configuration = new Configuration();
        $config        = $this->processConfiguration($configuration, $configs);

        $this->addConfigToRedirectSelectNotificationDefinition($container, $config);
        $this->addConfigToSelectAbstractSendPasswordDefinition($container, $config);
        $this->addConfigToSendNotificationDefinition($container, $config);
    }

    /**
     * Add config to the redirect select notification definition.
     *
     * @param ContainerBuilder $container The container.
     * @param array            $config    The config.
     *
     * @return void
     */
    private function addConfigToRedirectSelectNotificationDefinition(ContainerBuilder $container, array $config): void
    {
        $definition = $container->getDefinition(RedirectSelectNotification::class);
        $definition->setArgument('$sessionKey', $config['sessionKey']);
    }

    /**
     * Add config to the abstract send password definition.
     *
     * @param ContainerBuilder $container The container.
     * @param array            $config    The config.
     *
     * @return void
     */
    private function addConfigToSelectAbstractSendPasswordDefinition(ContainerBuilder $container, array $config): void
    {
        $definition = $container->getDefinition(AbstractSendPassword::class);
        $definition->setArgument('$sessionKey', $config['sessionKey']);
    }

    /**
     * Add config to the send notification definition.
     *
     * @param ContainerBuilder $container The container.
     * @param array            $config    The config.
     *
     * @return void
     */
    private function addConfigToSendNotificationDefinition(ContainerBuilder $container, array $config): void
    {
        $definition = $container->getDefinition(SendPasswordSendNotification::class);
        $definition->setArgument('$sessionKey', $config['sessionKey']);
        $definition->setArgument('$sendCount', $config['sendCount']);
        $definition->setArgument('$sendDelay', $config['sendDelay']);
    }
}
