<?php
/**
 * Created by PhpStorm.
 * User: hzy
 * Date: 2019/1/21
 * Time: 15:55
 * 日志函数相关
 */

require_once __DIR__ . '/db.php';

/**
 * @param string $msg
 * @throws Exception
 */
function stdlog(string $msg)
{
    global $db;
    $msg = $db->escape_string($msg);
    if (!$db->query("INSERT INTO `stdlog` (`msg`) VALUES ('{$msg}')"))
        throw new Exception('Database unavailable', -60);
}

/**
 * @param int $code
 * @param string $msg
 * @throws Exception
 */
function errlog(int $code, string $msg)
{
    global $db;
    $msg = $db->escape_string($msg);
    if (!$db->query("INSERT INTO `errorlog` (`code`,`msg`) VALUES ('{$code}','{$msg}')"))
        throw new Exception('Database unavailable', -60);
}