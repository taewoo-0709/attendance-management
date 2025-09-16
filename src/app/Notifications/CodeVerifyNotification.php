<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class CodeVerifyNotification extends Notification
{
    use Queueable;

    protected $code;

    public function __construct($code)
    {
        $this->code = $code;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('【認証コードのお知らせ】')
            ->line('以下の認証コードを入力してください。')
            ->line('認証コード: ' . $this->code)
            ->line('このコードの有効期限は10分です。');
    }
}