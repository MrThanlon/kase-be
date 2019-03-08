<?php
/**
 * Created by PhpStorm.
 * User: hzy
 * Date: 2019/2/24
 * Time: 20:13
 * 短信验证码功能
 */

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../include/exception.php';

use Qcloud\Sms\SmsSingleSender;

/**
 * @author hzy
 * @param int $scode
 * @param string $phone
 * @throws KBException
 * 短信验证码
 */
function scode(int $scode, string $phone)
{
    $sender = new SmsSingleSender(SMS_APPID, SMS_APPKEY);
    $res = $sender->sendWithParam('86', $phone, SMS_TPLID_SCODE, [(string)$scode]);
    $rsp = json_decode($res, true);
    if ($rsp['result'] !== 0)
        throw new KBException(-20);
}

/**
 * @param bool $is_pass
 * @param int $cid
 * @param string $name
 * @param string $phone
 * @throws KBException
 * 短信通知
 */
function snotice(bool $is_pass, int $cid, string $name, string $phone)
{
    $sender = new SmsSingleSender(SMS_APPID, SMS_APPKEY);
    $res = $sender->sendWithParam('86', $phone, SMS_TPLID_NOTICE, [
        $name,
        $is_pass ? '通过' : '被拒',
        (string)$cid
    ]);
    $rsp = json_decode($res, true);
    if ($rsp['result'] !== 0)
        throw new KBException(-20);
}

/**
 * @param string $phone
 * @param int $token
 * @throws KBException
 * 账户注册短信
 */
function sreg(string $phone, int $token)
{
    $sender = new SmsSingleSender(SMS_APPID, SMS_APPKEY);
    $res = $sender->sendWithParam('86', $phone, SMS_TPLID_NOTICE, [(string)$token]);
    $rsp = json_decode($res, true);
    if ($rsp['result'] !== 0)
        throw new KBException(-20);
}