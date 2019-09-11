<?php
/**
 * Created by PhpStorm.
 * User: hzy
 * Date: 2019/2/12
 * Time: 15:21
 * 查询分数
 */

try {
    require_once __DIR__ . '/../../include/jwt.php';
    require_once __DIR__ . '/../../include/check_cid.php';
    header('Content-type: application/json');
    if ($_SERVER['REQUEST_METHOD'] !== 'POST')
        //bad request
        throw new KBException(-100);
    if (!key_exists('token', $_COOKIE))
        throw new KBException(-10);
    $jwt = jwt_decode($_COOKIE['token']);
    if ($jwt['type'] !== 2)
        throw new KBException(-100);
    if (!key_exists('cid', $_POST) || !key_exists('pqid', $_POST))
        throw new KBException(-100);
    if (!preg_match("/^\d*?$/AD", $_POST['cid']) || !preg_match("/^\d*?$/AD", $_POST['pqid']))
        throw new KBException(-100);
    $cid = (int)$_POST['cid'];
    $pqid = (int)$_POST['pqid'];
    check_cid($db, $cid, $jwt['uid']);
    //检测pqid
    $ans = $db->query("SELECT `pid` FROM `content` WHERE `cid`={$cid}");
    $res = $ans->fetch_row();
    $pid = $res[0];
    $ans = $db->query("SELECT 1 FROM `question` WHERE `pid`={$pid} AND `pqid`={$pqid}");
    if ($ans->num_rows === 0)
        throw new KBException(-100);
    //pqid存在，查询最终分数
    $score = 0;
    $ans = $db->query(
        "SELECT `score` FROM `score` WHERE `uid`={$jwt['uid']} AND `cid`={$cid} AND `pqid`= ORDER BY `id` DESC LIMIT 1");
    if ($ans->num_rows === 0)
        echo json_encode([
            'status' => 0,
            'msg' => '',
            'score' => $score
        ]);
    $res = $ans->fetch_row();
    $score = (int)$res[0];
    echo json_encode([
        'status' => 0,
        'msg' => '',
        'score' => $score
    ]);

} catch (KBException $e) {
    echo json_encode(['status' => $e->getCode(), 'msg' => $e->getMessage()]);
} catch (Exception $e) {
    echo json_encode(['status' => -200, 'msg' => 'Unknow error']);
}