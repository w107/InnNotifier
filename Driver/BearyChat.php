<?php

require_once __DIR__ . '/Notifier.php';

class BearyChat extends Notifier
{
    public function config(Typecho_Widget_Helper_Form $form)
    {
        $beary_chat_webhook = new Typecho_Widget_Helper_Form_Element_Text('beary_chat_webhook', NULL, NULL, _t('BearyChat Webhook'), '需要在 <a href="https://bearychat.com/">BearyChat</a> 注册<br/>同时，注册后需要添加一个 Incoming机器人 并配置Webhook才能收到推送');
        $form->addInput($beary_chat_webhook);
    }

    protected function send($title, $text)
    {
        $options = $this->getOptions();
        $webhook = $options->beary_chat_webhook;
        if (empty($webhook)) {
            return;
        }
        $webhook = str_ireplace('https://', 'http://', $webhook);
        $data = [
            'text' => $title,
            'attachments' => [
                [
                    'text' => $text,
                    'color' => '#ffa500',
                ]
            ],
        ];
        $curl = new Typecho_Http_Client_Adapter_Curl();
        $curl->setTimeout(2)
            ->setHeader('Content-Type', 'application/json')
            ->setData(json_encode($data))
            ->httpSend($webhook);
    }

    public function comment($comment, $post)
    {
        $text = "**{$comment['author']}** 在 [「{$post->title}」]({$post->permalink} \"{$post->title}\") 中说到: \n {$comment['text']}";
        $this->send('有人在您的博客发表了评论', $text);
    }

    public function register($dataStruct)
    {
        $options = Typecho_Widget::widget('Widget_Options');
        $title = $options->title;
        $url = $options->siteUrl;
        $text = "**{$dataStruct['name']}** 在 [「{$title}」]({$url}) 注册了: \n ";
        $text .= "用户名：{$dataStruct['name']}\n  ";
        $text .= "邮箱：{$dataStruct['mail']}";
        $this->send('您的博客有新用户注册', $text);
    }

}