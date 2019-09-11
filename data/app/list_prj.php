<?php
/**
 * Created by PhpStorm.
 * User: hzy
 * Date: 2019/2/4
 * Time: 10:46
 * 拉取项目列表
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
    if ($jwt['type'] !== 1)
        throw new KBException(-100);
    $ans = $db->query("SELECT `pid`,`name` FROM `project`");
    $data = [];
    for ($i = $ans->num_rows; $i > 0; $i--) {
        $d = $ans->fetch_assoc();
        $d['pid'] = (int)$d['pid'];
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
