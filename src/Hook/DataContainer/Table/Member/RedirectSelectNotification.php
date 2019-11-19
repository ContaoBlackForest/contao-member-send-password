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

namespace ContaoBlackForest\MemberSendPasswordBundle\Hook\DataContainer\Table\Member;

use Contao\BackendTemplate;
use Contao\CoreBundle\Exception\RedirectResponseException;
use Contao\Template;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;

/**
 * This redirect to select the notification route.
 */
final class RedirectSelectNotification
{
    /**
     * The request stack.
     *
     * @var RequestStack
     */
    private $request;

    /**
     * The session.
     *
     * @var SessionInterface
     */
    private $session;

    /**
     * The router.
     *
     * @var RouterInterface
     */
    private $router;

    /**
     * The token manager.
     *
     * @var CsrfTokenManagerInterface
     */
    private $tokenManager;

    /**
     * The token name.
     *
     * @var string
     */
    private $tokenName;

    /**
     * The session key.
     *
     * @var string
     */
    private $sessionKey;

    /**
     * The constructor.
     *
     * @param RequestStack              $request      The request stack.
     * @param SessionInterface          $session      The session.
     * @param RouterInterface           $router       The router.
     * @param CsrfTokenManagerInterface $tokenManager The token manager.
     * @param string                    $tokenName    The token name.
     * @param string                    $sessionKey   The session key.
     */
    public function __construct(
        RequestStack $request,
        SessionInterface $session,
        RouterInterface $router,
        CsrfTokenManagerInterface $tokenManager,
        string $tokenName,
        string $sessionKey
    ) {
        $this->request      = $request;
        $this->session      = $session;
        $this->router       = $router;
        $this->tokenManager = $tokenManager;
        $this->tokenName    = $tokenName;
        $this->sessionKey   = $sessionKey;
    }

    /**
     * Render the view for select the notification.
     *
     * @param Template $template The template.
     *
     * @return void
     */
    public function __invoke(Template $template): void
    {
        if (!($template instanceof BackendTemplate) || !$this->determineInvoke()) {
            return;
        }

        $this->session->remove($this->sessionKey);

        $request = $this->request->getCurrentRequest();

        $session = ['memberIds' => $request->request->get('IDS')];

        $this->session->set($this->sessionKey, $session);

        throw new RedirectResponseException(
            $this->router->generate(
                'contao_cb_member_send_passoword_notification',
                [
                    'rt' => $this->tokenManager->getToken($this->tokenName)->getValue(),
                    'ref' => $request->attributes->get('_contao_referer_id')
                ]
            )
        );
    }

    /**
     * Determine for invoke this.
     *
     * @return bool
     */
    private function determineInvoke(): bool
    {
        $request = $this->request->getCurrentRequest();

        return $request
               && ('member' === $request->query->get('do'))
               && ('select' === $request->query->get('act'))
               && ('tl_select' === $request->request->get('FORM_SUBMIT'))
               && $request->request->has('password')
               && $request->request->has('IDS');
    }
}
