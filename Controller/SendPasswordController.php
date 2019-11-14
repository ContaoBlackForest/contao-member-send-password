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

namespace ContaoBlackForest\Member\SendPassword\Controller;

use Contao\BackendTemplate;
use Contao\Config;
use Contao\Controller;
use Contao\Email;
use Contao\Environment;
use Contao\MemberModel;
use Contao\PageModel;
use Contao\Template;

/**
 * The send password controller.
 */
class SendPasswordController
{
    public function parseTemplate(Template $backendTemplate)
    {
        if ($backendTemplate->getName() !== 'be_main') {
            return;
        }

        $session    = new SessionController();
        $currentIds = $session->getCurrentIds();

        if (!$currentIds) {
            return;
        }

        $processIds = $currentIds;
        if ($session->getProcessedMembers()) {
            $processIds = array_diff($currentIds, $session->getProcessedMembers());
        }

        $processIds = array_slice($processIds, 0, 10);

        $memberModel = MemberModel::findMultipleByIds($processIds);

        $loginPage = PageModel::findByPk(87);

        if ($memberModel) {
            $template = new BackendTemplate('be_member_send_password');

            $tableMember = new \tl_member();

            $email = new Email();

            $members = array();
            while ($memberModel->next()) {
                $members[] = $memberModel->current();

                $password = substr(str_shuffle('abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'), 0, 8);

                $memberModel->password = $tableMember->setNewPassword(
                    \Encryption::hash($password),
                    $memberModel->current()
                );
                $memberModel->current()->save();

                $email->text    = vsprintf(
                    'Hallo %s %s,
                    
                    für den Zugang in das Intranet des Musikforum Durlach(%s) benötigst Du diese Zugangsdaten:
                    
                    Login: %s
                    Passwort: %s
                    
                    Mit freundlichen Grüßen
                    %s',
                    array(
                        $memberModel->firstname,
                        $memberModel->lastname,
                        $loginPage->getAbsoluteUrl(),
                        $memberModel->username ? $memberModel->username : $memberModel->email,
                        $password,
                        Config::get('websiteTitle')
                    )
                );
                $email->subject = Config::get('websiteTitle') . ': Es wurde ein neues Passwort erstellt';
                $email->sendTo($memberModel->email);

                $session->setProcessedMember($memberModel->id);
            }

            $template->members = $members;
            $template->count   = count($currentIds);
            $template->current = count((array) $session->getProcessedMembers());


            $backendTemplate->main = $template->parse();
            $backendTemplate->mootools .=
                "<script>location.href = '" . Environment::get('request') . "'</script>";

            return;
        }

        $session->removeSession();

        Controller::redirect(explode('?', Environment::get('request'))[0] . '?do=member');
    }
}
