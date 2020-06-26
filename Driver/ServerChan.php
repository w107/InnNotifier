<?php

require_once __DIR__ . '/Notifier.php';

class ServerChan extends Notifier
{
    public function config(Typecho_Widget_Helper_Form $form)
    {
        $server_chan_sckey = new Typecho_Widget_Helper_Form_Element_Text('server_chan_sckey', NULL, NULL, _t('ServerChan key'), 'SCKEY 需要在 <a href="http://sc.ftqq.com/">Server酱</a> 注册<br/>同时，注册后需要在 <a href="http://sc.ftqq.com/">Server酱</a> 绑定你的微信号才能收到推送');
        $form->addInput($server_chan_sckey);
    }

    protected function send($text, $desp)
    {
        $options = $this->getOptions();
        $sckey = $options->server_chan_sckey;
        if (empty($sckey)) {
            return;
        }

        $curl = new Typecho_Http_Client_Adapter_Curl();
        $curl->setTimeout(2)->setData([
            'text' => $text,
            'desp' => $desp,
        ])->httpSend("http://sc.ftqq.com/{$sckey}.send");
    }

    public function comment($comment, $post)
    {
        $desp = "**{$comment['author']}** 在 [「{$post->title}」]({$post->permalink} \"{$post->title}\") 中说到: \n\n > {$comment['text']}";
        $this->send('有人在您的博客发表了评论', $desp);
    }

    public function register($dataStruct)
    {
        $options = Typecho_Widget::widget('Widget_Options');
        $title = $options->title;
        $url = $options->siteUrl;
        $desp = "**{$dataStruct['name']}** 在 [「{$title}」]({$url}) 注册了: \n\n ";
        $desp .= "> 用户名：{$dataStruct['name']}\n  ";
        $desp .= "邮箱：{$dataStruct['mail']}";
        $this->send('您的博客有新用户注册', $desp);
    }
}