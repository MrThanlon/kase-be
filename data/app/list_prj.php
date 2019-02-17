<?php
/**
 * Created by PhpStorm.
 * User: hzy
 * Date: 2019/2/4
 * Time: 10:46
 * 拉取项目列表
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
    if ($jwt['type'] !== 1)
        throw new KBException(-100);
    $ans = $db->query("SELECT `pid`,`name` FROM `project`");
    $data = [];
    for ($i = $ans->num_rows; $i > 0; $i--) {
        $data[] = $ans->fetch_assoc();
    }
    echo json_encode([
        'status_code' => 0,
        'msg' => '',
        'data' => $data
    ]);

} catch (KBException $e) {
    echo json_encode(['status_code' => $e->getCode(), 'msg' => $e->getMessage()]);
} catch (Exception $e) {
    echo json_encode(['status_code' => -200, 'msg' => 'Unknow error']);
}
