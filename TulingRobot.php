<?php
/**
 * Message: 依赖 - 图灵机器人
 * 地址： http://www.tuling123.com
 * User: jzc
 * Date: 2019/6/5
 * Time: 7:47 PM
 * Return:
 */

namespace App;


class TulingRobot
{
    const API_KEY = '';//创建图灵机器人后获取
    const USER_ID = '';//注册图灵账号后获取
    const TULING_URL = 'http://openapi.tuling123.com/openapi/api/v2';//图灵接口地址

    /**
     * 获取回复消息
     * @param $msg
     * @return array
     */
    public function getReply($msg)
    {
        $data = [
            'reqType' => 0,
            'perception' => [
                'inputText' => [
                    'text' => !empty($msg['Content']) ? $msg['Content'] : ''
                ],
                'inputImage' => [
                    'url' => !empty($msg['PicUrl']) ? $msg['PicUrl'] : ''
                ],
                'selfInfo' => [
                    'location' => [
                        'city' => '',
                        'province' => '',
                        'street' => ''
                    ]
                ]
            ],
            'userInfo' => [
                'apiKey' => self::API_KEY,
                'userId' => self::USER_ID
            ],
        ];

        $data = $this->postRequest(self::TULING_URL, $data);

        $rs = [];
        if (!empty($data)) {
            $data = json_decode($data, true);
            $rs = $data['results']['values']['text'];
        }

        return $rs;
    }

    /**
     * 这里简单起见直接采用原生POST请求
     * @param $url
     * @param $postData
     * @return bool|string
     */
    public function postRequest($url, $postData)
    {
        $postData = http_build_query($postData);
        $options = [
            'method' => 'POST',
            'header' => 'Content-type: text/plain',
            'content' => $postData,
            'timeout' => 10
        ];

        $context = stream_context_create($options);
        $result = file_get_contents($url, false, $context);

        return $result;
    }
}