<?php
/**
 * Created by PhpStorm.
 * User: hzy
 * Date: 2019/2/17
 * Time: 15:10
 * 修改项目信息
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
    if ($jwt['type'] !== 3)
        throw new KBException(-100);
    if (!key_exists('pid', $_POST) || !preg_match("/^[1-9]\d*$/AD", $_POST['pid']) ||
        !key_exists('name', $_POST) || !key_exists('total', $_POST) ||
        !key_exists('total_only', $_POST) || !preg_match("/^[1-9]\d*$/AD", $_POST['total']) ||
        ($_POST['total_only'] !== '1' && $_POST['total_only'] !== '2'))
        throw new KBException(-100);
    $pid = (int)$_POST['pid'];
    $name = $db->escape_string($_POST['name']);
    $total = (int)$_POST['total'];
    $total_only = $_POST['total_only'] === '1' ? 1 : 2;
    $ans = $db->query("SELECT 1 FROM `project` WHERE `pid`={$pid}");
    if ($ans->num_rows === 0)
        throw new KBException(-101);
    $db->query("UPDATE `project` SET `name`='{$name}',`total`={$total},`total_only`={$total_only} WHERE `pid`={$pid} LIMIT 1");
    if ($db->sqlstate !== '00000')
        throw new KBException(-60);
    echo json_encode(['status' => 0, 'msg' => '']);

} catch (KBException $e) {
    echo json_encode(['status_code' => $e->getCode(), 'msg' => $e->getMessage()]);
} catch (Exception $e) {
    echo json_encode(['status_code' => -200, 'msg' => 'Unknow error']);
}