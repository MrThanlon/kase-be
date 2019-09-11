<?php
/**
 * Created by PhpStorm.
 * User: hzy
 * Date: 2019/1/26
 * Time: 10:26
 * 用户身份API
 */

try {
    require_once __DIR__ . '/../include/jwt.php';
    header('Content-type: application/json');
    if ($_SERVER['REQUEST_METHOD'] !== 'POST')
        //bad request
        throw new KBException(-100);
    if (!key_exists('token', $_COOKIE))
        throw new KBException(-10);
    $jwt = jwt_decode($_COOKIE['token']);
    $ans = $db->query("SELECT `tel` FROM `user` WHERE `uid`={$jwt['uid']}")->fetch_row();
    echo json_encode([
        'status' => 0,
        'msg' => '',
        'type' => $jwt['type'],
        'uid' => $jwt['uid'],
        'tel' => (int)$ans[0]
    ]);

} catch (KBException $e) {
    echo json_encode(['status' => $e->getCode(), 'msg' => $e->getMessage()]);
} catch (Exception $e) {
    echo json_encode(['status' => -200, 'msg' => 'Unknow error']);
}
