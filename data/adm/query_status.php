<?php
/**
 * Created by PhpStorm.
 * User: hzy
 * Date: 2019/9/23
 * Time: 22:36
 * 拉取评分状态
 */

try {
    require_once __DIR__ . '/../../include/jwt.php';
    header('Content-type: application/json');


} catch (KBException $e) {
    echo json_encode(['status' => $e->getCode(), 'msg' => $e->getMessage()]);
} catch (Exception $e) {
    echo json_encode(['status' => -200, 'msg' => 'Unknow error']);
}