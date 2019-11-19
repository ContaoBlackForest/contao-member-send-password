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

namespace ContaoBlackForest\MemberSendPasswordBundle\Controller;

use ContaoBlackForest\MemberSendPasswordBundle\View\Backend\SendPasswordSelectNotification;
use ContaoBlackForest\MemberSendPasswordBundle\View\Backend\SendPasswordSend;
use ContaoBlackForest\MemberSendPasswordBundle\View\Backend\SendPasswordSendNotification;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * The send password controller.
 *
 * @Route(defaults={"_scope" = "backend", "_token_check" = "true"})
 */
class SendPasswordController extends AbstractController
{
    /**
     * The notification action, for select the notification.
     *
     * @Route("/contao/cb/member/send_password/notification",
     *      name="contao_cb_member_send_passoword_notification")
     */
    public function notificationAction(): Response
    {
        $this->container->get('contao.framework')->initialize();

        return $this->container->get(SendPasswordSelectNotification::class)->process();
    }

    /**
     * The send action, for starting send the notification.
     *
     * @Route("/contao/cb/member/send_password/send",
     *      name="contao_cb_member_send_passoword_send")
     */
    public function sendAction(): Response
    {
        $this->container->get('contao.framework')->initialize();

        return $this->container->get(SendPasswordSend::class)->process();
    }

    /**
     * The send notification action, for send the notification.
     *
     * @Route("/contao/cb/member/send_password/send/notification",
     *      name="contao_cb_member_send_passoword_send_notification")
     */
    public function sendNotificationAction(): Response
    {
        $this->container->get('contao.framework')->initialize();

        return $this->container->get(SendPasswordSendNotification::class)->process();
    }
}
