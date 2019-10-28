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
 * @param string $scode
 * @param string $phone
 * @param int $tplid
 * @throws KBException
 * 短信验证码
 * @author hzy
 */
function scode(string $scode, string $phone, int $tplid)
{
    $sender = new SmsSingleSender(SMS_APPID, SMS_APPKEY);
    $res = $sender->sendWithParam('86', $phone, $tplid, [$scode]);
    $rsp = json_decode($res, true);
    if ($rsp['result'] !== 0)
        throw new KBException(-20, $res);
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
 * @throws KBException
 * 发送注册短信
 */
function sreg(string $phone)
{
    $scode = sprintf("%06d", mt_rand(0, 999999));
    scode($scode, $phone, SMS_TPLID_REGISTE);
    setcookie(
        'sms_token',
        $enc = sms_encode($scode, $phone, 'register'),
        time() + SMS_PERIOD,
        PATH,
        DOMAIN
    );
}

/**
 * @param string $phone
 * @throws KBException
 * 发送登录短信
 */
function slogin(string $phone)
{
    $scode = sprintf("%06d", mt_rand(0, 999999));
    scode($scode, $phone, SMS_TPLID_LOGIN);
    setcookie(
        'sms_token',
        $enc = sms_encode($scode, $phone, 'login'),
        time() + SMS_PERIOD,
        PATH,
        DOMAIN
    );
}

/**
 * @param string $scode
 * @param string $phone
 * @param string $use
 * @return string
 * 验证码，发送到cookie，使用JWT方式，包含验证码哈希、手机号、生成时间、有效期、用途(register/login)
 */
function sms_encode(string $scode, string $phone, string $use)
{
    // FIXME: 使用同一个hmac secet可能有风险
    $t = time();
    $data = [
        'scode' => hash_hmac('sha256', $scode . $phone . $t . SMS_PERIOD . $use, SMS_SECRET),
        'phone' => $phone,
        'born' => $t,
        'period' => SMS_PERIOD,
        'use' => $use
    ];
    $data_str = base64_encode(json_encode($data));
    $hash = hash_hmac('sha256', $data_str, SMS_SECRET);
    return $data_str . $hash;
}

/**
 * @param string $token
 * @param string $scode
 * @param string $phone
 * @param string $use
 * @throws KBException
 * 短信验证码校验
 */
function sms_check(string $token, string $phone, string $scode, string $use)
{
    //截取数据和签名
    $hash = substr($token, -64);
    $data_str = substr($token, 0, -64);
    //验证签名
    if (hash_hmac('sha256', $data_str, SMS_SECRET) !== $hash)
        throw new KBException(-12);
    //解析
    $data_dec = base64_decode($data_str);
    if ($data_dec === false)
        throw new KBException(-200, "Failed to parse sms token data from cookie");
    $data = json_decode($data_dec, true);
    if ($data === null)
        throw new KBException(-200, "Failed to parse sms token data from cookie");
    //校验
    $t = $data['born'];
    $period = $data['period'];
    if ($data['scode'] !== hash_hmac('sha256', $scode . $phone . $t . $period . $use, SMS_SECRET) ||
        $data['born'] + $data['period'] <= time() ||
        $data['phone'] !== $phone)
        throw new KBException(-12);
}