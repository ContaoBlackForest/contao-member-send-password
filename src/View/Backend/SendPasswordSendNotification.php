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

use Contao\Controller;
use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\CoreBundle\Monolog\ContaoContext;
use Contao\Environment as ContaoEnvironment;
use Contao\MemberModel;
use Contao\PageModel;
use Doctrine\DBAL\Connection;
use NotificationCenter\Model\Notification;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use TrueBV\Punycode;
use Twig\Environment;

/**
 * This send the password notification to the member.
 */
final class SendPasswordSendNotification
{
    /**
     * The contao framework.
     *
     * @var ContaoFramework
     */
    private $framework;

    /**
     * The database connection.
     *
     * @var Connection
     */
    private $connection;

    /**
     * The twig engine.
     *
     * @var Environment
     */
    private $twig;

    /**
     * The session.
     *
     * @var SessionInterface
     */
    private $session;

    /**
     * The token manager.
     *
     * @var CsrfTokenManagerInterface
     */
    private $tokenManager;

    /**
     * The logger.
     *
     * @var LoggerInterface
     */
    private $logger;

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
     * The send count.
     *
     * @var int
     */
    private $sendCount;

    /**
     * The send delay.
     *
     * @var int
     */
    private $sendDelay;

    /**
     * The constructor.
     *
     * @param ContaoFramework           $framework    The contao framework.
     * @param Connection                $connection   The database connection.
     * @param Environment               $twig         The twig engine.
     * @param SessionInterface          $session      The session.
     * @param CsrfTokenManagerInterface $tokenManager The token manager.
     * @param LoggerInterface           $logger       The logger.
     * @param string                    $tokenName    The token name.
     * @param string                    $sessionKey   The session key.
     * @param int                       $sendCount    The send count.
     * @param int                       $sendDelay    The send delay.
     */
    public function __construct(
        ContaoFramework $framework,
        Connection $connection,
        Environment $twig,
        SessionInterface $session,
        CsrfTokenManagerInterface $tokenManager,
        LoggerInterface $logger,
        string $tokenName,
        string $sessionKey,
        int $sendCount,
        int $sendDelay
    ) {
        $this->framework    = $framework;
        $this->connection   = $connection;
        $this->twig         = $twig;
        $this->session      = $session;
        $this->tokenManager = $tokenManager;
        $this->logger       = $logger;
        $this->tokenName    = $tokenName;
        $this->sessionKey   = $sessionKey;
        $this->sendCount    = $sendCount;
        $this->sendDelay    = $sendDelay;
    }

    /**
     * Process the send password.
     *
     * @return Response
     */
    public function process(): Response
    {
        if (!$this->determineProcess()) {
            $response          = [];
            $response['error'] = $this->twig->render(
                '@BlackForestMemberSendPassword/Backend/be_error.html.twig',
                [
                    'messages'     => [
                        'MSC.errorNoNotification',
                        'MSC.errorNoSelectMemberIds',
                        'MSC.errorNoPageSelected'
                    ],
                    'requestToken' => $this->tokenManager->getToken($this->tokenName)->getValue(),
                ]
            );

            return new JsonResponse($response);
        }

        $session = $this->session->get($this->sessionKey);

        if (!isset($session['step'])) {
            $session['step'] = 0;
        }
        if (!isset($session['executed'])) {
            $session['executed'] = [];
        }

        $memberIds = \array_slice($session['memberIds'], ($session['step'] * $this->sendCount), $this->sendCount);
        $this->sendPassword($memberIds, $session['notification'], $session['passwordPage']);

        $session['executed'] = \array_merge($session['executed'], $memberIds);
        ++$session['step'];
        $this->session->set($this->sessionKey, $session);

        $progress = (100 / \count($session['memberIds'])) * \count($session['executed']);
        return new JsonResponse(['progress' => $progress, 'sendDelay' => $this->sendDelay * 100]);
    }

    /**
     * Send the password to the member.
     *
     * @param array $memberIds      The member ids.
     * @param int   $notificationId The notification id.
     * @param int   $passwordPage   The password page.
     *
     * @return void
     */
    private function sendPassword(array $memberIds, int $notificationId, int $passwordPage): void
    {
        /** @var Notification $notification */
        $notification     = $this->framework->getAdapter(Notification::class)->findByPk($notificationId);
        $flattenDelimiter = $notification->flatten_delimiter;

        /** @var PageModel $page */
        $page = $this->framework->getAdapter(PageModel::class)->findByPk($passwordPage);
        $page->loadDetails();

        $this->framework->getAdapter(Controller::class)->loadLanguageFile('tl_member');
        $this->framework->getAdapter(Controller::class)->loadDataContainer('tl_member');
        $memberLabelTokens = \array_map(
            function ($property, $config) {
                if (!isset($config['label'][0])) {
                    return ['member_label_' . $property => $property];
                }

                return ['member_label_' . $property => $config['label'][0]];
            },
            \array_keys($GLOBALS['TL_DCA']['tl_member']['fields']),
            $GLOBALS['TL_DCA']['tl_member']['fields']
        );

        $this->framework->getAdapter(Controller::class)->loadLanguageFile('tl_page');
        $this->framework->getAdapter(Controller::class)->loadDataContainer('tl_page');
        $pageLabelTokens = \array_map(
            function ($property, $config) {
                if (!isset($config['label'][0])) {
                    return ['page_label_' . $property => $property];
                }

                return ['page_label_' . $property => $config['label'][0]];
            },
            \array_keys($GLOBALS['TL_DCA']['tl_page']['fields']),
            $GLOBALS['TL_DCA']['tl_page']['fields']
        );
        $pageTokens      = \array_map(
            function ($property, $value) use ($flattenDelimiter) {
                if (\is_string($value)
                    && \is_array($values = @\unserialize($value, ['allowed_classes' => false]))) {
                    return ['page_' . $property => \implode($flattenDelimiter, $values)];
                }

                return ['page_' . $property => $value];
            },
            \array_keys($page->row()),
            $page->row()
        );

        $environment   = $this->framework->getAdapter(ContaoEnvironment::class);
        $punyCode      = new Punycode();
        $defaultTokens = \array_merge(
            [
                'domain' => $page->domain
                    ? $punyCode->decode($page->domain)
                    : $punyCode->decode($environment->get('host')),
                'link'   => $page->getAbsoluteUrl()
            ],
            ...$memberLabelTokens,
            ...$pageLabelTokens,
            ...$pageTokens
        );

        /** @var MemberModel $memberModel */
        $memberModel = $this->framework->getAdapter(MemberModel::class);
        foreach ($memberIds as $memberId) {
            /** @var MemberModel $member */
            $member             = $memberModel->findByPk($memberId);
            $member->activation = 'PW' . \substr(\md5(\uniqid((string) \mt_rand(), true)), 2);
            $member->save();

            $this->logger->log(
                LogLevel::INFO,
                \sprintf(
                    'A new password has been requested for user ID %s (%s)',
                    $member->id,
                    $punyCode->encode($member->email)
                ),
                ['contao' => new ContaoContext('MemberSendPasswordToken', LogLevel::INFO)]
            );

            $memberTokens = \array_map(
                function ($property, $value) use ($flattenDelimiter) {
                    if (\is_string($value)
                        && \is_array($values = @\unserialize($value, ['allowed_classes' => false]))
                    ) {
                        return ['member_' . $property => \implode($flattenDelimiter, $values)];
                    }

                    return ['member_' . $property => $value];
                },
                \array_keys($member->row()),
                $member->row()
            );

            $tokens = \array_merge($defaultTokens, ...$memberTokens);

            $tokens['link']     .= '?token=' . $member->activation;
            $tokens['raw_data'] = \implode(
                PHP_EOL,
                \array_map(
                    function ($key, $value) {
                        return $key . ': ' . $value;
                    },
                    \array_keys($tokens),
                    $tokens
                )
            );

            $notification->send($tokens, $page->language);
        }
    }

    /**
     * Determine for process this.
     *
     * @return bool
     */
    private function determineProcess(): bool
    {
        $session = (array) $this->session->get($this->sessionKey);

        return isset($session['memberIds'], $session['notification'], $session['passwordPage'])
               && \count($session['memberIds']);
    }
}
