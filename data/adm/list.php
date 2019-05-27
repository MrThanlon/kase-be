<?php
/**
 * Created by PhpStorm.
 * User: hzy
 * Date: 2019/2/14
 * Time: 13:59
 * 拉取项目列表，全部的
 */

try {
    header('Content-type: application/json');
    require_once '../../include/jwt.php';
    if ($_SERVER['REQUEST_METHOD'] !== 'POST')
        //bad request
        throw new KBException(-100);
    if (!key_exists('token', $_COOKIE))
        throw new KBException(-10);
    $jwt = jwt_decode($_COOKIE['token']);
    if ($jwt['type'] !== 3)
        throw new KBException(-100);
    $ans = $db->query("SELECT `name`,`pid`,`groups`,`contents` FROM `project`");
    $data = [];
    for ($i = $ans->num_rows; $i > 0; $i--) {
        $d = $ans->fetch_assoc();
        $d['pid'] = (int)$d['pid'];
        $d['groups'] = (int)$d['groups'];
        $d['contents'] = (int)$d['contents'];
        $data[] = $d;
    }
    echo json_encode([
        'status' => 0,
        'msg' => '',
        'data' => $data
    ]);

} catch (KBException $e) {
    echo json_encode(['status' => $e->getCode(), 'msg' => $e->getMessage()]);
} catch (Exception $e) {
    echo json_encode(['status' => -200, 'msg' => 'Unknow error']);
}