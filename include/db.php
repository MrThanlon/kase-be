<?php
/**
 * Created by PhpStorm.
 * User: hzy
 * Date: 2019/1/21
 * Time: 15:51
 */

require_once __DIR__ . '/../config.php';
$db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT);
if ($db->connect_errno)
    throw new Exception('Database unavailable', -60);
if ($db->errno)
    throw new Exception('Database unavailable', -60);
