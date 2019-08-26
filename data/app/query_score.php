<?php
/**
 * 申请人接口-查询分数
 */

try {
    header('Content-type: application/json');
    require '../../include/jwt.php';
    if ($_SERVER['REQUEST_METHOD'] !== 'POST')
        //bad request
        throw new KBException(-100);
    if (!key_exists('token', $_COOKIE))
        throw new KBException(-10);
    if (!key_exists('cid', $_POST) || !preg_match("/^\d*?$/AD", $_POST['cid']))
        throw new KBException(-100);

    $jwt = jwt_decode($_COOKIE['token']);
    if ($jwt['type'] !== 1)
        throw new KBException(-100);
    //查找cid
    $cid = (int)$_POST['cid'];
    $ans = $db->query("SELECT `pid` FROM `content` WHERE `cid`={$cid} LIMIT 1");
    if ($ans->num_rows === 0)
        throw new KBException(-103);
    $pid = (int)(($ans->fetch_row())[0]);

    //拉取题目
    $ans = $db->query("SELECT `qid`,`name` FROM `question` WHERE `pid`={$pid}");
    $questions = $ans->fetch_all();

    //拉取分数
    $ans = $db->query("SELECT `score` FROM `score`");


    //响应
    echo json_encode([
        'status' => 0,
        'msg' => '',
        'data' => ''
    ]);

} catch (KBException $e) {
    echo json_encode(['status' => $e->getCode(), 'msg' => $e->getMessage()]);
} catch (Exception $e) {
    echo json_encode(['status' => -200, 'msg' => 'Unknow error']);
}
