<?php
/**
 * jwt解析
 * ©hzy
 */

require_once __DIR__ . '/exception.php';

if (CORS) {
    header('Access-Control-Allow-Origin: ' . CORS);
    header('Access-Control-Allow-Credentials: true');
}
ini_set('display_errors', 0);

/**
 * @param string
 * @return bool
 * 验证token的签名
 * @author hzy
 */
function jwt_check(string $token)
{
    if (strlen($token) <= 65)
        return false;
    $sign = substr($token, -64);
    $token = substr($token, 0, -64);
    //验证签名
    if (hash_hmac('sha256', $token, SECRET) !== $sign)
        return false;
    return true;
}

/**
 * @param string
 * @return mixed
 * 解析token
 * @throws KBException
 * @author hzy
 */
function jwt_decode(string $token)
{
    if (jwt_check($token) === false)
        throw new KBException(-10);
    //解析JWT
    $token = substr($token, 0, -64);
    $token = base64_decode($token, true);
    if ($token === false)
        throw new KBException(-10);
    $token_arr = json_decode($token, true);
    if ($token_arr === null)
        throw new KBException(-10);
    //验证version
    global $db;
    $ans = $db->query("SELECT `version` FROM `user` WHERE `uid`={$token_arr['uid']}");
    if ($ans->num_rows === 0)
        throw new KBException(-10);
    $res = $ans->fetch_row();
    if ($token_arr['version'] < (int)$res[0])
        throw new KBException(-10);
    //验证时间
    if (time() >= $token_arr['expire'])
        throw new KBException(-10);
    return $token_arr;
}

/**
 * @param array
 * @return string
 * 编码token+签名
 * @author hzy
 */
function jwt_encode(array $arr)
{
    $token = base64_encode(json_encode($arr));
    return $token . hash_hmac('sha256', $token, SECRET);
}