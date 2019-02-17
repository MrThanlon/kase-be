<?php
/**
 * Created by PhpStorm.
 * User: hzy
 * Date: 2019/2/14
 * Time: 14:09
 * 创建项目
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
    if (!key_exists('name', $_POST) || !key_exists('total', $_POST) ||
        !key_exists('total_only', $_POST) || !preg_match("/^[1-9]\d*$/AD", $_POST['total']) ||
        ($_POST['total_only'] !== '1' && $_POST['total_only'] !== '2'))
        throw new KBException(-100);
    $name = $db->escape_string($_POST['name']);
    $total = (int)$_POST['total'];
    $total_only = $_POST['total_only'] === '1' ? 1 : 2;
    if ($name === '')
        throw new KBException(-100);
    $db->query(
        "INSERT INTO `project` (`name`,`total`,`total_only`) VALUES ('{$name}',{$total},{$total_only})");
    if ($db->sqlstate !== '00000')
        //插入失败
        throw new KBException(-60);
    echo json_encode(['status' => 0, 'msg' => '']);

} catch (KBException $e) {
    echo json_encode(['status_code' => $e->getCode(), 'msg' => $e->getMessage()]);
} catch (Exception $e) {
    echo json_encode(['status_code' => -200, 'msg' => 'Unknow error']);
}