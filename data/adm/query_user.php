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
    $ans = $db->query("SELECT ")

} catch (KBException $e) {
    echo json_encode(['status' => $e->getCode(), 'msg' => $e->getMessage()]);
} catch (Exception $e) {
    echo json_encode(['status' => -200, 'msg' => 'Unknow error']);
}
