<?php

require_once __DIR__ . '/Notifier.php';

class Qmsg extends Notifier
{
    public function config(Typecho_Widget_Helper_Form $form)
    {
        $qmsg_api = new Typecho_Widget_Helper_Form_Element_Text('qmsg_api', NULL, NULL, 'Qmsg接口地址', '需要在 <a href="https://qmsg.zendee.cn/api/">Qmsg酱</a> 注册<br/>同时绑定你的QQ才能收到推送');
        $form->addInput($qmsg_api);
        $qmsg_qq = new Typecho_Widget_Helper_Form_Element_Text('qmsg_qq', NULL, NULL, 'QQ', '指定接收消息的QQ，可以添加多个，以英文逗号分割。如：1244453393,2952937634（指定的QQ必须在您的QQ号列表中）');
        $form->addInput($qmsg_qq);
    }

    protected function send($msg)
    {
        $options = $this->getOptions();
        $qmsg_api = $options->qmsg_api;
        $qmsg_qq = $options->qmsg_qq;
        if (empty($qmsg_api) || empty($qmsg_qq)) {
            return;
        }

        $context  = stream_context_create([
            'http' => [
                'method'  => 'POST',
                'timeout' => 2,
                'header'  => 'Content-type: application/x-www-form-urlencoded',
                'content' => http_build_query([
                    'msg' => $msg,
                    'qq' => $qmsg_qq,
                ])
            ]
        ]);
        file_get_contents($qmsg_api, false, $context);
    }

    public function comment($comment, $post)
    {
        $msg = "{$comment['author']} 在「{$post->title}」({$post->permalink}) 中说到: {$comment['text']}";
        $this->send($msg);
    }

    public function register($dataStruct)
    {
        $options = Typecho_Widget::widget('Widget_Options');
        $msg = "「{$dataStruct['name']}」 在你的博客({$options->siteUrl})注册了";
        $this->send($msg);
    }

}