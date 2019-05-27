***REMOVED***
***REMOVED***
***REMOVED***
***REMOVED***
 * Date: 2019/2/17
 * Time: 11:25
 * 拉取通知
***REMOVED***

try {
    header('Content-type: application/json');
    require '../../include/jwt.php';
    if ($_SERVER['REQUEST_METHOD'] !== 'POST')
        //bad request
        throw new KBException(-100);
    if (!key_exists('token', $_COOKIE))
        throw new KBException(-10);
    $jwt = jwt_decode($_COOKIE['token']);
    if ($jwt['type'] !== 3)
        throw new KBException(-100);
    $ans = $db->query("SELECT `value` FROM `normal` WHERE `nid`=1 LIMIT 1");
    echo json_encode([
        'status' => 0,
        'msg' => '',
        'content' => ($ans->fetch_row())[0]
    ]);
} catch (KBException $e) {
    echo json_encode(['status' => $e->getCode(), 'msg' => $e->getMessage()]);
} catch (Exception $e) {
    echo json_encode(['status' => -200, 'msg' => 'Unknow error']);
}