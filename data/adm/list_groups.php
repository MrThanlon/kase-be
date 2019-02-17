<?php
/**
 * Created by PhpStorm.
 * User: hzy
 * Date: 2019/2/14
 * Time: 14:13
 * 拉取材料组
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
    if (!key_exists('pid', $_POST))
        throw new KBException(-100);
    if (!preg_match("/^\d*?$/AD", $_POST['pid']))
        throw new KBException(-100);
    $pid = (int)$_POST['pid'];
    $ans = $db->query("SELECT `gid`,`contents`,`users` FROM `pgroup` WHERE `pid`={$pid}");
    $data = [];
    for ($i = $ans->num_rows; $i > 0; $i--) {
        $d = $ans->fetch_assoc();
        $d['gid'] = (int)$d['gid'];
        $d['users'] = (int)$d['users'];
        $d['contents'] = (int)$d['contents'];
        $data[] = $d;
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