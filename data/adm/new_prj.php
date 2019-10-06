<?php
/**
 * Created by PhpStorm.
 * User: hzy
 * Date: 2019/2/14
 * Time: 14:09
 * 创建项目
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
    if (!key_exists('name', $_POST) ||
        !key_exists('start', $_POST) ||
        !key_exists('end', $_POST) ||
        !preg_match("/^[1-9]\d*$/AD", $_POST['start']) ||
        !preg_match("/^[1-9]\d*$/AD", $_POST['end'])
    )
        throw new KBException(-100);
    $name = $db->escape_string($_POST['name']);
    $start = $_POST['start'];
    $end = $_POST['end'];

    if ($name === '')
        throw new KBException(-100);
    $db->query("INSERT INTO `project` (`name`,`start`,`end`) VALUES ('{$name}',FROM_UNIXTIME({$start}),FROM_UNIXTIME({$end}))");
    if ($db->sqlstate !== '00000')
        //插入失败
        throw new KBException(-60);
    echo json_encode(['status' => 0, 'msg' => '']);

} catch (KBException $e) {
    echo json_encode(['status' => $e->getCode(), 'msg' => $e->getMessage()]);
} catch (Exception $e) {
    echo json_encode(['status' => -200, 'msg' => 'Unknow error']);
}