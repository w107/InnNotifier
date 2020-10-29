<?php

require_once __DIR__ . '/Notifier.php';

class IGot extends Notifier
{
    public function config(Typecho_Widget_Helper_Form $form)
    {
        $qmsg_api = new Typecho_Widget_Helper_Form_Element_Text('igot_key', NULL, NULL, 'iGot key', '多种推送方式 , 已支持Bark（ios），邮箱，微信
支持桌面客户端（Windows & Mac）、utools、 微信小程序、 快捷指令<br>需要在 <a href="https://github.com/wahao/Bark-MP-helper/">iGot</a> 获取推送key才能收到推送');
        $form->addInput($qmsg_api);
    }

    protected function send($title, $content, $url = '')
    {
        $options = $this->getOptions();
        $igot_key = $options->igot_key;

        if (empty($igot_key)) {
            return;
        }

        $data = [
            'title' => $title,
            'content' => $content,
            'url' => $url,
        ];
        $curl = new Typecho_Http_Client_Adapter_Curl();
        $curl->setTimeout(2)
            ->setHeader('Content-Type', 'application/json')
            ->setData(json_encode($data))
            ->httpSend("http://push.hellyw.com/{$igot_key}");
    }

    public function comment($comment, $post)
    {
        $msg = "{$comment['author']} 在「{$post->title}」中说到: {$comment['text']}";
        $this->send('有人在您的博客发表了评论', $msg, $post->permalink);
    }

    public function register($dataStruct)
    {
        $options = Typecho_Widget::widget('Widget_Options');
        $msg = "「{$dataStruct['name']}」 在你的博客注册了";
        $this->send('您的博客有新用户注册', $msg, $options->siteUrl);
    }

}