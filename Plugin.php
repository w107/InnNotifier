<?php

/**
 * 推送评论、注册通知，支持多种通知方式
 *
 * @package InnNotifier
 * @author Inn
 * @version 1.1.0
 * @link https://gog5.cn
 */
class InnNotifier_Plugin implements Typecho_Plugin_Interface
{

    protected static $support_map = [
        'ServerChan' => 'Server酱(微信推送)',
        'BearyChat' => 'BearyChat',
        'Qmsg' => 'Qmsg酱(QQ推送)',
        'IGot' => 'iGot',
    ];

    protected static $type_map = [
        'comment' => '评论通知',
        'register' => '注册通知',
    ];

    /**
     * @return mixed
     * @throws Typecho_Exception
     */
    protected static function getOptions()
    {
        return Typecho_Widget::widget('Widget_Options')->plugin('InnNotifier');
    }

    /**
     * 激活插件方法,如果激活失败,直接抛出异常
     * @return string
     */
    public static function activate()
    {
        Typecho_Plugin::factory('Widget_Feedback')->comment = array(__CLASS__, 'comment');
        Typecho_Plugin::factory('Widget_Feedback')->trackback = array(__CLASS__, 'comment');
        Typecho_Plugin::factory('Widget_XmlRpc')->pingback = array(__CLASS__, 'comment');
        Typecho_Plugin::factory('Widget_Register')->register = array(__CLASS__, 'register');

        return _t('请配置此插件的参数, 以使您的推送生效');
    }

    /**
     * 禁用插件方法,如果禁用失败,直接抛出异常
     */
    public static function deactivate(){}

    /**
     * 获取插件配置面板
     * @param Typecho_Widget_Helper_Form $form 配置面板
     * @return void
     * @throws Typecho_Exception
     */
    public static function config(Typecho_Widget_Helper_Form $form)
    {
        $supports = new Typecho_Widget_Helper_Form_Element_Checkbox('supports', self::$support_map, null, '支持');
        $form->addInput($supports);
        $types = new Typecho_Widget_Helper_Form_Element_Checkbox('types', self::$type_map, null, '通知类型');
        $form->addInput($types);

        self::tap(function (Notifier $notifier) use ($form) {
            $form->addItem((new Typecho_Widget_Helper_Layout())->html('<hr>'));
            $notifier->config($form);
        }, array_keys(self::$support_map));
    }

    /**
     * 个人用户的配置面板
     *
     * @param Typecho_Widget_Helper_Form $form
     */
    public static function personalConfig(Typecho_Widget_Helper_Form $form){}

    /**
     * @param $comment
     * @param $post
     * @return mixed
     * @throws Typecho_Exception
     */
    public static function comment($comment, $post)
    {
        self::tapWhenEnable('comment', function (Notifier $notifier) use ($comment, $post) {
            $notifier->comment($comment, $post);
        });
        return $comment;
    }

    /**
     * @param $dataStruct
     * @return mixed
     * @throws Typecho_Exception
     */
    public static function register($dataStruct)
    {
        self::tapWhenEnable('register', function (Notifier $notifier) use ($dataStruct) {
            $notifier->register($dataStruct);
        });
        return $dataStruct;
    }

    /**
     * @param closure $closure
     * @param array $supports
     * @throws Typecho_Exception
     */
    protected static function tap(closure $closure, $supports = [])
    {
        if (empty($supports)) {
            $supports = self::getOptions()->supports;
        }
        foreach ($supports as $support) {
            require_once __DIR__ . "/Driver/{$support}.php";
            $closure(new $support());
        }
    }

    /**
     * @param string $type
     * @param closure $closure
     * @throws Typecho_Exception
     */
    protected static function tapWhenEnable($type, closure $closure)
    {
        in_array($type, self::getOptions()->types) && self::tap($closure);
    }

}
