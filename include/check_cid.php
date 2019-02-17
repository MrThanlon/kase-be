<?php
/**
 * Created by PhpStorm.
 * User: hzy
 * Date: 2019/2/12
 * Time: 15:01
 * 用于检测cid与uid是否匹配于一个group
 */

/**
 * @param mysqli $db
 * @param int $cid
 * @param int $uid
 * @throws KBException
 * @author hzy
 */
function check_cid(mysqli $db,int $cid,int $uid){
    //先查找材料是否允许被此账号评审
    //首先检查pid和状态
    $ans = $db->query("SELECT `pid`,`status` FROM `content` WHERE `cid`={$cid} LIMIT 1");
    if ($ans->num_rows === 0)
        throw new KBException(-103);
    $res = $ans->fetch_row();
    $status = (int)$res[1];
    $pid = (int)$res[0];
    if ($status !== 1)//检查状态
        throw new KBException(-100);
    //查找对应的pid
    $ans = $db->query("SELECT 1 FROM `user-project` WHERE `pid`={$pid} AND `uid`={$uid}");
    if ($ans->num_rows === 0)
        throw new KBException(-103);
    //检查是否在group
    //查找content所有所属group
    $cg = [];
    $ans = $db->query("SELECT `gid` FROM `content-group` WHERE `cid`={$cid}");
    $res = $ans->fetch_all();
    //key存储，打表快速查找
    foreach ($res as $val) {
        $cg[(int)$val[0]] = 1;
    }
    //查找user所有所属group
    $ug = [];
    $flag = false;
    $ans = $db->query("SELECT `gid` FROM `user-group` WHERE `uid`={$uid}");
    $res = $ans->fetch_all();
    foreach ($res as $val) {
        $ug[] = (int)$val[0];
        if (isset($cg[(int)$val[0]])) {
            $flag = true;
            break;
        }
    }
    if (!$flag)
        throw new KBException(-103);
}