<?php
/**
 * Created by PhpStorm.
 * User: hzy
 * Date: 2019/2/14
 * Time: 14:13
 * 拉取材料组
 *
 * update: 2019/10/5 修改功能，直接提取分区下的课题和评审
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
    if (!key_exists('pid', $_POST))
        throw new KBException(-100);
    if (!preg_match("/^\d*?$/AD", $_POST['pid']))
        throw new KBException(-100);
    $pid = (int)$_POST['pid'];
    $ans = $db->query("SELECT `gid` FROM `pgroup` WHERE `pid`={$pid}");
    $gids = $ans->fetch_all();

    // 拉取分区下的课题
    $ans = $db->query("SELECT `cid`,`gid` FROM `content-group` WHERE `gid` IN (SELECT `gid` FROM `pgroup` WHERE `pid`={$pid})");
    $cg = $ans->fetch_all();

    // 数据结构转换
    $content_group = array_reduce($cg, function ($pre, $cur) {
        $pre[(int)$cur[1]][] = (int)$cur[0];
        return $pre;
    }, []);

    // 拉取所有评审uid-username
    $ans = $db->query("SELECT `uid`,`username` FROM `user` WHERE `type`=2");
    $jugs = $ans->fetch_all();

    // 数据结构转换
    $jug_username = array_reduce($jugs, function ($pre, $cur) {
        $pre[(int)$cur[0]] = $cur[1];
        return $pre;
    }, []);

    // 拉取分区下的评审
    $ans = $db->query("SELECT `uid`,`gid` FROM `user-group` WHERE `gid` IN (SELECT `gid` FROM `pgroup` WHERE `pid`={$pid})");
    $ug = $ans->fetch_all();

    // 数据结构转换
    $user_group = array_reduce($ug, function ($pre, $cur) {
        global $jug_username;
        $pre[(int)$cur[1]][] = $jug_username[(int)$cur[0]];
        return $pre;
    }, []);

    // 合成数据
    $data = array_map(function ($val) {
        global $user_group;
        global $content_group;
        if ($user_group[(int)$val[0]] === null)
            $user_group[(int)$val[0]] = [];
        if ($content_group[(int)$val[0]] === null)
            $content_group[(int)$val[0]] = [];
        return [
            'gid' => (int)$val[0],
            'eva' => $user_group[(int)$val[0]],
            'content' => $content_group[(int)$val[0]]
        ];
    }, $gids);

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