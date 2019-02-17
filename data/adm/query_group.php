<?php
/**
 * Created by PhpStorm.
 * User: hzy
 * Date: 2019/2/14
 * Time: 14:23
 * 查询材料组详细
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
    if ($jwt['type'] !== 3 || !key_exists('gid', $_POST) || !preg_match("/^\d*?$/AD", $_POST['gid']))
        throw new KBException(-100);
    $gid = $_POST['gid'];
    $data = ['eva' => [], 'content' => []];

    $ans = $db->query("SELECT `uid` FROM `user-group` WHERE `gid`={$gid}");
    for ($i = $ans->num_rows; $i > 0; $i--) {
        $data['eva'][] = (int)$ans->fetch_row()[0];
    }

    $ans = $db->query("SELECT `cid` FROM `content-group` WHERE `gid`={$gid}");
    for ($i = $ans->num_rows; $i > 0; $i--) {
        $data['content'][] = (int)$ans->fetch_row()[0];
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