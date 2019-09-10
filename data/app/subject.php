<?php
/**
 * Created by PhpStorm.
 * User: hzy
 * Date: 2019/9/8
 * Time: 16:41
 * 拉取单个课题
 */

try {
    header('Content-type: application/json');
    require '../../include/jwt.php';
    if ($_SERVER['REQUEST_METHOD'] !== 'POST')
        //bad request
        throw new KBException(-100);
    if (!key_exists('cid', $_POST))
        throw new KBException(-100);
    if (!key_exists('token', $_COOKIE))
        throw new KBException(-10);
    $jwt = jwt_decode($_COOKIE['token']);
    if ($jwt['type'] !== 1)
        throw new KBException(-100);
    $cid = $db->escape_string($_POST['cid']);
    $ans = $db->query(
        "SELECT `name`,`cid`,`pid`,`applicant`,`status`,`time`,`pdf_name`,`zip_name` FROM `content` WHERE `uid`={$jwt['uid']} AND `cid`={$cid}");
    if($ans->num_rows === 0)
        throw new KBException(-103);
    // FIXME: 使用array_map
    $d = $ans->fetch_assoc();
    $d['cid'] = (int)$d['cid'];
    $d['pid'] = (int)$d['pid'];
    $d['status'] = (int)$d['status'];
    if ($d['pdf_name'] == null) {
        $d['pdf'] = false;
    } else
        $d['pdf'] = true;
    if ($d['zip_name'] == null) {
        $d['zip'] = false;
    } else
        $d['zip'] = true;
    unset($d['pdf_name']);
    unset($d['zip_name']);
    //响应
    echo json_encode([
        'status' => 0,
        'msg' => '',
        'data' => $d
    ]);

} catch (KBException $e) {
    echo json_encode(['status' => $e->getCode(), 'msg' => $e->getMessage()]);
} catch (Exception $e) {
    echo json_encode(['status' => -200, 'msg' => 'Unknow error']);
}

