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

use Contao\CoreBundle\Framework\ContaoFramework;
use Doctrine\DBAL\Connection;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\Translation\TranslatorInterface;
use Twig\Environment;

/**
 * The view for the send the notification.
 */
final class SendPasswordSend extends AbstractSendPassword
{
    /**
     * The database connection.
     *
     * @var Connection
     */
    private $connection;

    /**
     * The request stack.
     *
     * @var RequestStack
     */
    private $request;

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
     * The constructor.
     *
     * @param Connection                $connection     The database connection.
     * @param RequestStack              $request        The request stack.
     * @param ContaoFramework           $framework      The framework.
     * @param TranslatorInterface       $translator     The translator.
     * @param Environment               $twig           The twig engine.
     * @param SessionInterface          $session        The session.
     * @param CsrfTokenManagerInterface $tokenManager   The token manager.
     * @param string                    $tokenName      The token name.
     * @param string                    $sessionKey     The session key.
     * @param array                     $kernelPackages The kernel packages.
     */
    public function __construct(
        Connection $connection,
        RequestStack $request,
        ContaoFramework $framework,
        TranslatorInterface $translator,
        Environment $twig,
        SessionInterface $session,
        CsrfTokenManagerInterface $tokenManager,
        string $tokenName,
        string $sessionKey,
        array $kernelPackages
    ) {
        parent::__construct($framework, $translator, $twig, $session, $sessionKey, $kernelPackages);

        $this->connection   = $connection;
        $this->request      = $request;
        $this->tokenManager = $tokenManager;
        $this->tokenName    = $tokenName;
    }

    /**
     * Process the send password.
     *
     * @return Response
     */
    public function process(): Response
    {
        $this->setupBackendTemplate('be_main');

        $this->backendTemplate->headline =
            $this->translator->trans('tl_member.send_password_headline', [], 'contao_tl_member');
        $this->backendTemplate->title    = $this->backendTemplate->headline;

        if (!$this->determineProcess()) {
            $this->backendTemplate->main = $this->twig->render(
                '@BlackForestMemberSendPassword/Backend/be_error.html.twig',
                [
                    'displayButtons' => true,
                    'messages'       => [
                        'MSC.errorNoNotification',
                        'MSC.errorNoSelectMemberIds',
                        'MSC.errorNoPageSelected'
                    ],
                    'requestToken'   => $this->tokenManager->getToken($this->tokenName)->getValue()
                ]
            );

            return $this->doProcess();
        }

        $request = $this->request->getCurrentRequest();
        $session = (array) $this->session->get($this->sessionKey);

        $session['notification'] = (int) $request->request->get('notification');
        $session['passwordPage'] = (int) $request->request->get('passwordPage');

        $this->addStyleSheet('backend/css/be_send_notification.css');
        $this->addJavascript('backend/js/be_send_notification.js');
        $this->backendTemplate->main = $this->twig->render(
            '@BlackForestMemberSendPassword/Backend/be_send_password_send.html.twig',
            [
                'notification' => $this->getNotificationTitle($session['notification']),
                'requestToken' => $this->tokenManager->getToken($this->tokenName)->getValue()
            ]
        );
        $this->session->set($this->sessionKey, $session);

        return $this->doProcess();
    }

    /**
     * Get the title of the notification.
     *
     * @param int $notificationId The notification id.
     *
     * @return string
     */
    private function getNotificationTitle(int $notificationId): string
    {
        $platform = $this->connection->getDatabasePlatform();

        $builder = $this->connection->createQueryBuilder();
        $builder
            ->select($platform->quoteIdentifier('id'), $platform->quoteIdentifier('title'))
            ->from($platform->quoteIdentifier('tl_nc_notification'))
            ->where($builder->expr()->eq($platform->quoteIdentifier('type'), ':type'))
            ->andWhere($builder->expr()->eq($platform->quoteIdentifier('id'), ':notificationId'))
            ->setParameter(':type', 'cb_member_send_password')
            ->setParameter(':notificationId', $notificationId)
            ->orderBy($platform->quoteIdentifier('title'));

        return $builder->execute()->fetch(\PDO::FETCH_OBJ)->title;
    }

    /**
     * Determine for process this.
     *
     * @return bool
     */
    private function determineProcess(): bool
    {
        $request = $this->request->getCurrentRequest();
        $session = (array) $this->session->get($this->sessionKey);

        return $request
               && isset($session['memberIds'])
               && \count($session['memberIds'])
               && $request->request->has('notification')
               && $request->request->has('passwordPage');
    }
}
