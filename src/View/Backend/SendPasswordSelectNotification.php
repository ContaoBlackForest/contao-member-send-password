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
use Contao\PageTree;
use Doctrine\DBAL\Connection;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\Translation\TranslatorInterface;
use Twig\Environment;

/**
 * The view for select the notification.
 */
final class SendPasswordSelectNotification extends AbstractSendPassword
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
     * @param ContaoFramework           $framework      The framework.
     * @param TranslatorInterface       $translator     The translator.
     * @param Environment               $twig           The twig engine.
     * @param SessionInterface          $session        The session.
     * @param Connection                $connection     The database connection.
     * @param RequestStack              $request        The request stack.
     * @param CsrfTokenManagerInterface $tokenManager   The token manager.
     * @param string                    $tokenName      The token name.
     * @param string                    $sessionKey     The session key.
     * @param array                     $kernelPackages The kernel packages.
     */
    public function __construct(
        ContaoFramework $framework,
        TranslatorInterface $translator,
        Environment $twig,
        SessionInterface $session,
        Connection $connection,
        RequestStack $request,
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
                    'messages'     => ['MSC.errorNoSelectMemberIds'],
                    'requestToken' => $this->tokenManager->getToken($this->tokenName)->getValue()
                ]
            );

            return $this->doProcess();
        }

        $request = $this->request->getCurrentRequest();
        if ($request 
            && $request->isXmlHttpRequest()
            && ('reloadPagetree' === $request->request->get('action'))
            && ('passwordPage' === $request->request->get('name'))
        ) {
            return new Response($this->generatePasswordPageWidget((int) $request->request->get('value')));
        }

        $this->addStyleSheet('backend/css/be_select_notification.css');
        $this->backendTemplate->main = $this->twig->render(
            '@BlackForestMemberSendPassword/Backend/be_send_password_select_notification.html.twig',
            [
                'requestToken'        => $this->tokenManager->getToken($this->tokenName)->getValue(),
                'notificationOptions' => $this->collectPropertyOptions(),
                'passwordPageWidget'  => $this->generatePasswordPageWidget()
            ]
        );

        return $this->doProcess();
    }

    private function generatePasswordPageWidget(int $value = null): string
    {
        $config = [
            'exclude'    => true,
            'inputType'  => 'pageTree',
            'foreignKey' => 'tl_page.title',
            'eval'       => ['fieldType' => 'radio']
        ];

        $attributes = $this->framework->getAdapter(PageTree::class)->getAttributesFromDca($config, 'passwordPage');

        if (null !== $value) {
            $attributes['value'] = $value;
        }

        return $this->framework->createInstance(PageTree::class, [$attributes])->generate();
    }

    /**
     * Collect the options for the property.
     *
     * @return array
     */
    private function collectPropertyOptions(): array
    {
        $platform = $this->connection->getDatabasePlatform();

        $builder = $this->connection->createQueryBuilder();
        $builder
            ->select($platform->quoteIdentifier('id'), $platform->quoteIdentifier('title'))
            ->from($platform->quoteIdentifier('tl_nc_notification'))
            ->where($builder->expr()->eq($platform->quoteIdentifier('type'), ':type'))
            ->setParameter(':type', 'cb_member_send_password')
            ->orderBy($platform->quoteIdentifier('title'));

        $statement = $builder->execute();
        if (!$statement->rowCount()) {
            return [];
        }

        $result = $statement->fetchAll(\PDO::FETCH_OBJ);

        $options = ['' => '-'];
        foreach ($result as $item) {
            $options[$item->id] = \sprintf(
                '%s [%s]',
                $item->title,
                $item->id
            );
        }

        return $options;
    }

    /**
     * Determine for process this.
     *
     * @return bool
     */
    private function determineProcess(): bool
    {
        $session = (array) $this->session->get($this->sessionKey);

        return isset($session['memberIds'])
               && \count($session['memberIds']);
    }
}
