<?php
/**
 * 配置文件
 */

namespace App;


class Config
{
    //插件配置
    const WX_TOKEN = '';//微信公众号你配置的TOKEN
    const API_KEY = '';//图灵机器人的API_KEY
    const USER_ID = '';//你在图灵机器人上注册账号的ID

    //特定条件或事件下回复该文案
    const HELP_STRING = "感谢关注*异次元程序员*！\n需要帮助请发送@帮助@；\n其他留言则由智能客服处理。感谢支持~~";
}