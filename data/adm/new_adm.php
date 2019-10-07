<?php
/**
 * Created by PhpStorm.
 * User: hzy
 * Date: 2019/2/14
 * Time: 14:38
 * 创建评审员账号
 */

try {
    require_once __DIR__ . '/../../include/jwt.php';
    header('Content-type: application/json');
    if ($_SERVER['REQUEST_METHOD'] !== 'POST')
        //bad request
        throw new KBException(-100);
    if (!key_exists('token', $_COOKIE))
        throw new KBException(-10);
    $jwt = jwt_decode($_COOKIE['token']);
    if ($jwt['type'] !== 3 || !key_exists('username', $_POST) || !key_exists('password', $_POST))
        throw new KBException(-100);

    $username = $_POST['username'];
    $password = $_POST['password'];
    if (preg_match("/^\d{11}$/AD", $username))
        throw new KBException(-100);
    if ($db->escape_string($username) !== $username)
        throw new KBException(-100);
    if (strpos($username, '<') !== false || strpos($username, '>') !== false)
        throw new KBException(-100);
    if (strlen($username) > 20)
        throw new KBException(-100);

    $username = $db->escape_string($username);
    //检查username是否已经存在
    $ans = $db->query("SELECT 1 FROM `user` WHERE `username`='{$username}'");
    if ($ans->num_rows !== 0)
        throw new KBException(-40);
    $password = hash('sha256', $password . HASH_SALT);
    $db->query("INSERT INTO `user` (`username`,`type`,`password`) VALUES ('{$username}',3,'{$password}')");
    if ($db->sqlstate !== '00000')
        throw new KBException(-60);
    echo json_encode(['status' => 0, 'msg' => '']);

} catch (KBException $e) {
    echo json_encode(['status' => $e->getCode(), 'msg' => $e->getMessage()]);
} catch (Exception $e) {
    echo json_encode(['status' => -200, 'msg' => 'Unknow error']);
}