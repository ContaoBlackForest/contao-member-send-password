<?php

/**
 * Copyright © ContaoBlackForest
 *
 * @package   contao-member-send-password
 * @author    Sven Baumann <baumann.sv@gmail.com>
 * @author    Dominik Tomasi <dominik.tomasi@gmail.com>
 * @license   GNU/LGPL
 * @copyright Copyright 2014-2016 ContaoBlackForest
 */

namespace ContaoBlackForest\Member\SendPassword\Controller;

use Contao\BackendTemplate;
use Contao\Config;
use Contao\Controller;
use Contao\Email;
use Contao\Environment;
use Contao\MemberModel;
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
                    
                    Es wurde ein neues Passwort erstellt.
                    
                    Login: %s
                    Passwort: %s
                    
                    Mit freundlichen Grüssen %s',
                    array(
                        $memberModel->firstname,
                        $memberModel->lastname,
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
