<?php
/**
 * Created by PhpStorm.
 * User: hzy
 * Date: 2019/9/26
 * Time: 15:08
 * 拉取所有评审账户
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

    // 拉取评审员username-uid
    $ans = $db->query("SELECT `uid`,`username` FROM `user` WHERE `type`=2");
    $u = $ans->fetch_all();

    $uids = array_reduce($u, function ($pre, $cur) {
        $pre[$cur[0]] = $cur[1];
        return $pre;
    }, []);
    // 拉取tables
    $ans = $db->query("SELECT `uid`,`time` FROM `tables` WHERE `uid` IN (SELECT `uid` FROM `user` WHERE `type`=2)");
    $t = $ans->fetch_all();
    $tables = array_reduce($t, function ($pre, $cur) {
        $pre[$cur[0]] = $cur[1];
        return $pre;
    });
    // 合成数据
    foreach ($u as &$val) {
        $uid = $val[0];
        $username = $val[1];
        $stat = key_exists($uid, $tables) ? true : false;

        $val = [
            'u' => $username,
            'stat' => $stat
        ];
        if ($stat) {
            $val['time'] = $tables[$uid];
        }
    }

    echo json_encode([
        'status' => 0,
        'msg' => '',
        'data' => $u
    ]);

} catch (KBException $e) {
    echo json_encode(['status' => $e->getCode(), 'msg' => $e->getMessage()]);
} catch (Exception $e) {
    echo json_encode(['status' => -200, 'msg' => 'Unknow error']);
}
