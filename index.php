<?php
/**
 * Message: 微信公众号智能机器人插件 - 欢迎关注微信公众号：异次元程序员 一起交流学习
 * User: jzc
 * Email: igetjzc@outlook.com
 * Blog： https://blog.csdn.net/qq_34694342
 * Date: 2019/6/5
 * Time: 7:22 PM
 * Return:
 */

namespace App;

use App\Lib\WxBizMsgCrypt;
use App\Config;

class index
{
    public function run()
    {
        $params = $_GET;
        if (empty($params)) {
            echo 'fail';
            exit();
        }

        //公众号第一次接入时的验证
        if (isset($params['echostr'])) {
            echo $this->valid($params);
            exit();
        }

        /*
         * 接收消息并处理
         * 处理失败后直接回复空字符串或者content为success
         */
        $data = file_get_contents("php://input");
        if (empty($data)) {
            echo '';
            exit();
        }

        //处理xml结构
        libxml_disable_entity_loader(true);
        $content = json_decode(json_encode(simplexml_load_string($data, 'SimpleXMLElement', LIBXML_NOCDATA)), true);

        //处理数据
        foreach ($content as $k => $v) {
            $content[$k] = $this->getRealValue($v);
        }

        /** 可添加用户管理逻辑 */

        /** 可添加消息记录逻辑 */


        /*
         * 事件推送 -- 订阅消息/取消订阅
         */
        if (isset($content['Event'])) {
            echo $this->transferMsg($content, Config::HELP_STRING);
            exit();
        }

        //消息解密 - 采用明文模式则不需要解密
        /*
        $msg = '';
        $pc = new WxBizMsgCrypt(WX_TOKEN, WX_AES_KEY, WX_APP_ID);
        $rs = $pc->decryptMsg($params['msg_signature'], $params['timestamp'], $params['nonce'], $data, $msg);
        Yii::warning('接收消息解密内容：'.$msg, CATEGORIES_WARN);
        if ($rs != 0) {
            echo '';
            exit();
        } else {
            $replyMsg = $this->dealMsg($msg);
        }
        */

        //消息类型处理
        //这里可以自己根据消息类型做定制化
        $returnMsg = '';
        switch ($content['MsgType']) {
            default :
                $returnMsg = $this->dealTextMsg($content);
                break;
        }

        $resMsg = $this->transferMsg($content, $returnMsg);//组合xml消息体

        //消息加密
        /*
        $time = time();
        $nonce = '123456';
        $pc->encryptMsg($replyMsg, $time, $nonce, $resMsg);
        $encryptResMsg = "<xml><Encrypt><![CDATA[{$resMsg}]></Encrypt><TimeStamp>{$time}</TimeStamp><Nonce>{$nonce}</Nonce></xml>";
        Yii::warning('加密后的回复消息xml:'.$encryptResMsg, CATEGORIES_WARN);
        */

        echo $resMsg;
    }

    /**
     * 接入验证
     * signature 微信加密签名
     * timestamp 时间戳
     * nonce 随机数
     * echostr 随机字符串
     * @param array $params
     * @return string
     */
    public function valid(array $params)
    {
        if (
            empty($params)
            || empty($params['timestamp'])
            || empty($params['nonce'])
            || empty($params['signature'])
            || empty($params['echostr'])
        ) {
            return '';
        }

        $tmpArray = array(Config::WX_TOKEN, $params['timestamp'], $params['nonce']);
        sort($tmpArray, SORT_STRING);
        $tmpStr = implode($tmpArray);
        $tmpStr = sha1($tmpStr);
        if ($params['signature'] == $tmpStr) {
            return $params['echostr'];//输出随机字符串
        }

        return '';
    }

    /**
     * 生成回复消息的xml格式
     * @param array $data
     * @param string $msg
     * @return bool
     */
    public function transferMsg($data = array(), $msg = '')
    {
        $format = "<xml>
<ToUserName><![CDATA[%s]]></ToUserName>
<FromUserName><![CDATA[%s]]></FromUserName>
<CreateTime>%s</CreateTime>
<MsgType><![CDATA[text]]></MsgType>
<Content><![CDATA[%s]]></Content>
</xml>";

        $result = sprintf($format, $data['FromUserName'], $data['ToUserName'], time(), $msg);
        return $result;
    }

    /**
     * 去除微信的外包层
     * @param $str
     * @return mixed
     */
    public function getRealValue($str)
    {
        if (strpos($str, '<![CDATA[') !== false) {
            $str = substr($str, 9);
        }

        if (strpos($str, ']]>') !== false) {
            $str = substr($str, 0, -3);
        }

        return $str;
    }

    /**
     * 处理消息
     * @param array $content
     * @return mixed|string
     */
    public function dealTextMsg($content)
    {
        $finalStr = '';

        //这里你可以定制各种各样的条件回复
        switch ($content['Content']) {
            //帮助
            case $content['Content'] == '@帮助@' :
                $finalStr = Config::HELP_STRING;
                break;
            //默认，人工智障
            default :
                $finalStr = (new TulingRobot())->getReply($content);
                break;
        }

        return $finalStr;
    }
}


(new index())->run();