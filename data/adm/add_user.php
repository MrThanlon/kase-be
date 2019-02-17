<?php
/**
 * Created by PhpStorm.
 * User: hzy
 * Date: 2019/2/14
 * Time: 14:38
 * 创建评审员账号
 */

try {
    header('Content-type: application/json');
    require '../../include/jwt.php';
    if ($_SERVER['REQUEST_METHOD'] !== 'POST')
        //bad request
        throw new KBException(-100);
    if (!key_exists('token', $_COOKIE))
        throw new KBException(-10);
    $jwt = jwt_decode($_COOKIE['token']);
    if ($jwt['type'] !== 3 || !key_exists('u', $_POST) || !key_exists('p', $_POST))
        throw new KBException(-100);
    if (preg_match("/^\d{11}$/AD", $_POST['u']))
        throw new KBException(-100);
    if ($db->escape_string($_POST['u']) !== $_POST['u'])
        throw new KBException(-100);
    if (strpos($_POST['u'], '<') !== false || strpos($_POST['u'], '>') !== false)
        throw new KBException(-100);
    if (strlen($_POST['u']) > 20)
        throw new KBException(-100);
    $u = $db->escape_string($_POST['u']);
    //检查username是否已经存在
    $ans = $db->query("SELECT 1 FROM `user` WHERE `username`={$u}");
    if ($ans->num_rows !== 0)
        throw new KBException(-40);
    $p = hash('sha256', $_POST['p'] . HASH_SALT);
    $db->query("INSERT INTO `user` (`username`,`type`,`password`) VALUES ('{$u}',2,'{$p}')");
    if ($db->sqlstate !== '00000')
        throw new KBException(-60);
    echo json_encode(['status' => 0, 'msg' => '']);

} catch (KBException $e) {
    echo json_encode(['status_code' => $e->getCode(), 'msg' => $e->getMessage()]);
} catch (Exception $e) {
    echo json_encode(['status_code' => -200, 'msg' => 'Unknow error']);
}