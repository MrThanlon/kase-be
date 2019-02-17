<?php
/**
 * Created by PhpStorm.
 * User: hzy
 * Date: 2019/2/12
 * Time: 13:50
 * 评分
 */

try {
    header('Content-type: application/json');
    require_once '../../include/jwt.php';
    require_once '../../include/check_cid.php';
    if ($_SERVER['REQUEST_METHOD'] !== 'POST')
        //bad request
        throw new KBException(-100);
    if (!key_exists('token', $_COOKIE))
        throw new KBException(-10);
    $jwt = jwt_decode($_COOKIE['token']);
    if ($jwt['type'] !== 2)
        throw new KBException(-100);
    if (!key_exists('cid', $_POST) || !key_exists('score', $_POST) || !key_exists('pqid', $_POST))
        throw new KBException(-100);
    if (!preg_match("/^\d{1,3}$/AD", $_POST['score']) || !preg_match("/^\d*?$/AD", $_POST['cid'])
        || !!preg_match("/^\d*?$/AD", $_POST['pqid']))
        throw new KBException(-100);
    $score = (int)$_POST['score'];
    $cid = (int)$_POST['cid'];
    $pqid = (int)$_POST['pqid'];
    if ($score > 100)
        throw new KBException(-100);
    check_cid($db, $cid, $jwt['uid']);
    //检查题目是否存在
    //查找pid
    $ans = $db->query("SELECT `pid` FROM `content` WHERE `cid`={$cid}");
    $res = $ans->fetch_row();
    $pid = (int)$res[0];
    $ans = $db->query("SELECT `qid`,`max` FROM `question` WHERE `pid`={$pid} AND `pqid`={$pqid}");
    if ($ans->num_rows === 0)
        throw new KBException(-100);
    $res = $ans->fetch_row();
    if ($score > (int)$res[1])
        throw new KBException(-100);
    //计入分数
    $db->query(
        "INSERT INTO `score` (`cid`,`uid`,`qid`,`pqid`,`score`,`time`) VALUES ({$cid},{$jwt['uid']},{$res[0]},{$pqid},{$score},CURRENT_TIMESTAMP())");
    if ($db->sqlstate !== '00000')//插入失败
        throw new KBException(-60);
    echo json_encode([
        'status_code' => 0,
        'msg' => ''
    ]);
} catch (KBException $e) {
    echo json_encode(['status_code' => $e->getCode(), 'msg' => $e->getMessage()]);
} catch (Exception $e) {
    echo json_encode(['status_code' => -200, 'msg' => 'Unknow error']);
}