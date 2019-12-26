<?php
/**
 * Created by PhpStorm.
 * User: hzy
 * Date: 2019/2/17
 * Time: 12:34
 * 查询评分内容
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
    if (!key_exists('pid', $_POST) || !preg_match("/^[1-9]\d*$/AD", $_POST['pid']))
        throw new KBException(-100);
    $pid = (int)$_POST['pid'];
    //拉取pid
    $ans = $db->query("SELECT `total_only` FROM `project` WHERE `pid`={$pid}");
    if ($ans->num_rows === 0)
        throw new KBException(-101);
    $res = $ans->fetch_row();
    $total_only = $res[0] === '1' ? true : false;
    $data = [];
    $total = 0;
    //拉取question
    $ans = $db->query(
        "SELECT `qid`,`pqid`,`name`,`comment`,`max` FROM `question` WHERE `pid`={$pid}");
    for ($i = $ans->num_rows; $i > 0; $i--) {
        $d = $ans->fetch_assoc();
        $d['qid'] = (int)$d['qid'];
        $d['pqid'] = (int)$d['pqid'];
        $d['max'] = (int)$d['max'];
        $total += $d['max'];
        $data[] = $d;
    }
    echo json_encode([
        'status' => 0, 'msg' => '',
        'total' => $total,
        'total_only' => $total_only,
        'data' => $data
    ]);
} catch (KBException $e) {
    echo json_encode(['status' => $e->getCode(), 'msg' => $e->getMessage()]);
} catch (Exception $e) {
    echo json_encode(['status' => -200, 'msg' => 'Unknow error']);
}