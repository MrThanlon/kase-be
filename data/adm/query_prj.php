<?php
/**
 * 拉取项目
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
        !preg_match("/^[1-9]\d*$/AD", $_POST['pid'])
    )
        throw new KBException(-100);

    $pid = (int)$_POST['pid'];
    $ans = $db->query("SELECT `name`,UNIX_TIMESTAMP(start),UNIX_TIMESTAMP(end) FROM `project` WHERE `pid`={$pid} LIMIT 1");
    if ($ans->num_rows === 0)
        throw new KBException(-101);

    $res = $ans->fetch_row();
    $data = [
        'name' => $res[0],
        'start' => (int)$res[1],
        'end' => (int)$res[2]
    ];
    echo json_encode(['status' => 0, 'msg' => '', 'data' => $data]);

} catch (KBException $e) {
    echo json_encode(['status' => $e->getCode(), 'msg' => $e->getMessage()]);
} catch (Exception $e) {
    echo json_encode(['status' => -200, 'msg' => 'Unknow error']);
}