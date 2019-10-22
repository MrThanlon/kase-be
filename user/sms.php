<?php
/**
 * Created by PhpStorm.
 * User: hzy
 * Date: 2019/2/25
 * Time: 13:01
 * 请求验证码
 */

try {
    require_once __DIR__ . '/../include/jwt.php';
    require_once __DIR__ . '/../include/sms.php';
    header('Content-type: application/json');
    if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !key_exists('u', $_POST) ||
        !preg_match("/^1[3|5|7|8]\d{9}$/AD", $_POST['u'])) //匹配手机号
        //bad request
        throw new KBException(-100);
    $u = (int)$_POST['u'];
    //检查是否存在
    $ans = $db->query("SELECT 1 FROM `user` WHERE `username`='{$u}' AND (`type`=1 OR `type`=0) LIMIT 1");
    if ($ans->num_rows === 0)
        throw new KBException(-40);
    //短信发送
    slogin($u);
    //响应
    echo json_encode(['status' => 0, 'msg' => '']);

} catch (KBException $e) {
    echo json_encode(['status' => $e->getCode(), 'msg' => $e->getMessage()]);
} catch (Exception $e) {
    echo json_encode(['status' => -200, 'msg' => 'Unknow error']);
}