<?php

/**
 *@copyright : ASk. < http://arshresume.epizy.com/ >
 *@author	 : Arshdeep Singh < arshdeepsinghjoshan84@gmail.com >
 *
 * All Rights Reserved.
 * Proprietary and confidential :  All information contained herein is, and remains
 * the property of ASK. and its partners.
 * Unauthorized copying of this file, via any medium is strictly prohibited.
 *
 */

namespace Modules\Smtp\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Mail;
use App\Models\User;
use Modules\Notification\Http\Controllers\NotificationController;
use Modules\Notification\Http\Models\Notification;
use Modules\Smtp\Http\Models\MailConfiguration;

class MailController extends Controller
{
    public static function sendMail($view, $userModel)
    {
        try {
            $config = MailConfiguration::findActive()->latest()->first();

            if ($config) {
                config([
                    'mail.mailers.smtp.host' => $config->host,
                    'mail.mailers.smtp.port' => $config->port,
                    'mail.mailers.smtp.username' => $config->username,
                    'mail.mailers.smtp.password' => $config->password,
                    'mail.mailers.smtp.encryption' => $config->encryption,
                    'mail.from.address' => $config->from_address,
                    'mail.from.name' => env('APP_NAME', false),
                ]);
                $emailData = [
                    'from' => $userModel->email,
                    'to' => env('MAIL_FROM_ADDRESS', false),
                    'subject' => 'New User Registerd Successfully',
                    'cc' => '',
                    'bcc' => '',
                    'content' => '',
                    'type_id' => null,
                    'model_id' => $userModel->id
                ];
                SmtpEmailQueueController::store($emailData);

                Mail::send($view, ['model'=>$userModel], function ($message) use ($userModel) {
                    $message->to($userModel->email);
                    $message->subject('New User Registerd Successfully');
                });
            }
            return true;
        } catch (\Exception $e) {

            $bug = $e->getMessage();
            $notification =  [
                'title' => 'Configuration Error',
                'description' => $bug,
                'model_id' => $userModel->id,
                'model_type' => User::class,
                'is_read' => Notification::IS_NOT_READ
            ];
            NotificationController::store($notification);
            return false;
        }
    }
}
