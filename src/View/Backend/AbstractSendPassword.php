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

namespace ContaoBlackForest\MemberSendPasswordBundle\View\Backend;

use Contao\BackendMain;
use Contao\BackendTemplate;
use Contao\CoreBundle\Framework\ContaoFramework;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Translation\TranslatorInterface;
use Twig\Environment;

/**
 * The common backend view.
 */
abstract class AbstractSendPassword
{
    /**
     * The framework.
     *
     * @var ContaoFramework
     */
    protected $framework;

    /**
     * The translator.
     *
     * @var TranslatorInterface
     */
    protected $translator;

    /**
     * The twig engine.
     *
     * @var Environment
     */
    protected $twig;

    /**
     * The session.
     *
     * @var SessionInterface
     */
    protected $session;

    /**
     * The session key.
     *
     * @var string
     */
    protected $sessionKey;

    /**
     * The kernel packages.
     *
     * @var array
     */
    protected $kernelPackages;

    /**
     * The backend main.
     *
     * @var BackendMain
     */
    protected $backendMain;

    /**
     * The backend template.
     *
     * @var BackendTemplate
     */
    protected $backendTemplate;

    /**
     * The constructor.
     *
     * @param ContaoFramework     $framework      The framework.
     * @param TranslatorInterface $translator     The translator.
     * @param Environment         $twig           The twig engine.
     * @param SessionInterface    $session        The session.
     * @param string              $sessionKey     The session key.
     * @param array               $kernelPackages The kernel packages.
     */
    public function __construct(
        ContaoFramework $framework,
        TranslatorInterface $translator,
        Environment $twig,
        SessionInterface $session,
        string $sessionKey,
        array $kernelPackages
    ) {
        $this->framework      = $framework;
        $this->translator     = $translator;
        $this->twig           = $twig;
        $this->session        = $session;
        $this->sessionKey     = $sessionKey;
        $this->kernelPackages = $kernelPackages;
    }

    /**
     * Process the send password.
     *
     * @return Response
     */
    abstract public function process(): Response;

    /**
     * Do process.
     *
     * @return Response
     *
     * @throws \ReflectionException If the class or method does not exist.
     */
    protected function doProcess(): Response
    {
        $reflection = new \ReflectionMethod(\get_class($this->backendMain), 'output');
        $reflection->setAccessible(true);
        return $reflection->invoke($this->backendMain);
    }

    /**
     * Add the style sheet to the template.
     *
     * @param string $path The path of the style sheet.
     *
     * @return void
     */
    protected function addStyleSheet(string $path): void
    {
        $stylesheet = $this->twig->render(
            '@BlackForestMemberSendPassword/Backend/html_tag_link.html.twig',
            [
                'path'        => $path,
                'packageName' => 'black_forest_member_send_password'
            ]
        );

        if (!$this->backendTemplate->stylesheets) {
            $this->backendTemplate->stylesheets = $stylesheet;

            return;
        }

        $this->backendTemplate->stylesheets .= PHP_EOL . $stylesheet;
    }

    /**
     * Add the javascript to the template.
     *
     * @param string $path The path of the javascript.
     *
     * @return void
     */
    protected function addJavascript(string $path): void
    {
        $javascript = $this->twig->render(
            '@BlackForestMemberSendPassword/Backend/html_tag_script.html.twig',
            [
                'path'        => $path,
                'packageName' => 'black_forest_member_send_password'
            ]
        );

        if (!$this->backendTemplate->javascripts) {
            $this->backendTemplate->javascripts = $javascript;

            return;
        }

        $this->backendTemplate->javascripts .= PHP_EOL . $javascript;
    }

    /**
     * Setup the backend template.
     *
     * @param string $templateName The template name.
     *
     * @return void
     *
     * @throws \ReflectionException If the class or property does not exist.
     */
    protected function setupBackendTemplate(string $templateName): void
    {
        $this->setupBackendMain();

        $this->backendTemplate =
            $this->framework->createInstance(BackendTemplate::class, [$templateName]);

        $reflection = new \ReflectionProperty(\get_class($this->backendMain), 'Template');
        $reflection->setAccessible(true);
        $reflection->setValue($this->backendMain, $this->backendTemplate);

        $packages = $this->kernelPackages;

        $this->backendTemplate->version =
            $this->translator->trans('MSC.version', [], 'contao_default') . ' ' . $packages['contao/core-bundle'];

        $this->backendTemplate->main  = '';
        $this->backendTemplate->theme = 'flexible';
    }

    /**
     * Setup the backend main.
     *
     * @return void
     */
    private function setupBackendMain(): void
    {
        $this->backendMain = $this->framework->createInstance(BackendMain::class);
    }
}
