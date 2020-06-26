<?php

abstract class Notifier
{
    protected $plugin = 'InnNotifier';

    abstract public function comment($comment, $post);

    abstract public function register($dataStruct);

    abstract public function config(Typecho_Widget_Helper_Form $form);

    protected function getOptions()
    {
        return Typecho_Widget::widget('Widget_Options')->plugin($this->plugin);
    }

}