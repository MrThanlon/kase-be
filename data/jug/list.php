<?php
/**
 * Created by PhpStorm.
 * User: hzy
 * Date: 2019/2/12
 * Time: 11:45
 * 拉取材料列表
 * 首先从user-group表中读取对应gid，然后读取所有cid
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
    if ($jwt['type'] !== 2)
        throw new KBException(-100);
    //拉取所有gid
    $ans = $db->query("SELECT `gid` FROM `user-group` WHERE `uid`={$jwt['uid']}");
    $res = $ans->fetch_all();
    if ($res === []) {
        //未分配
        echo json_encode([
            'status' => 0,
            'msg' => '',
            'data' => []
        ]);
        exit;
    }
    foreach ($res as &$v1) {
        $v1 = (int)$v1[0];
    }
    //拉取所有cid
    $cids = [];
    //合成WHERE语句
    $WHERE_str = '';
    foreach ($res as $val) {
        $WHERE_str .= "`gid`={$val} OR ";
    }
    $WHERE_str = substr($WHERE_str, 0, -4);
    $ans = $db->query("SELECT `cid` FROM `content-group` WHERE {$WHERE_str}");
    $res = $ans->fetch_all();
    //添加到cids，因为可能有重复的cid，所以用这个结构
    foreach ($res as $val) {
        $cids[$val[0]] = 1;
    }
    //从cid拉取content
    $data = [];
    //合成WHERE语句
    $WHERE_str = '';
    foreach ($cids as $key => $val) {
        $WHERE_str .= "`cid`={$key} OR ";
    }
    $WHERE_str = substr($WHERE_str, 0, -4);
    $ans = $db->query("SELECT `name`,`cid`,`applicant`,`status`,`pid` FROM `content` WHERE {$WHERE_str}");
    for ($i = $ans->num_rows; $i > 0; $i--) {
        $d = $ans->fetch_assoc();
        $d['cid'] = (int)$d['cid'];
        $d['pid'] = (int)$d['pid'];
        $d['status'] = (int)$d['status'];
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