<?php
/**
 * Created by PhpStorm.
 * User: hzy
 * Date: 2019/2/17
 * Time: 15:10
 * 修改项目信息
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
    if ($jwt['type'] !== 3)
        throw new KBException(-100);
    if (!key_exists('pid', $_POST) ||
        !preg_match("/^[1-9]\d*$/AD", $_POST['pid']) ||
        !key_exists('name', $_POST) ||
        !key_exists('start', $_POST) ||
        !key_exists('end', $_POST) ||
        !preg_match("/^[1-9]\d*$/AD", $_POST['start']) ||
        !preg_match("/^[1-9]\d*$/AD", $_POST['end']))
        throw new KBException(-100);

    $pid = (int)$_POST['pid'];
    $name = $db->escape_string($_POST['name']);
    $start = (int)$_POST['start'];
    $end = (int)$_POST['end'];
    $total_only = $_POST['total_only'] === 'true' ? 1 : 0;

    $ans = $db->query("SELECT 1 FROM `project` WHERE `pid`={$pid}");
    if ($ans->num_rows === 0)
        throw new KBException(-101);

    $db->query("UPDATE `project` SET `name`='{$name}',`start`=FROM_UNIXTIME({$start}),`end`=FROM_UNIXTIME({$end}),`total_only`={$total_only} WHERE `pid`={$pid} LIMIT 1");
    if ($db->error)
        throw new KBException(-60, $db->error);
    echo json_encode(['status' => 0, 'msg' => '']);

} catch (KBException $e) {
    echo json_encode(['status' => $e->getCode(), 'msg' => $e->getMessage()]);
} catch (Exception $e) {
    echo json_encode(['status' => -200, 'msg' => 'Unknow error']);
}