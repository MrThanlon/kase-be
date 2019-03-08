<?php
/**
 * Created by PhpStorm.
 * User: hzy
 * Date: 2019/2/17
 * Time: 15:17
 * 上传打分表，解析表中数据
 */

require_once __DIR__ . '/../../include/jwt.php';
require_once __DIR__ . '/../../include/letter_sheet.php';

try {
    header('Content-type: application/json');
    if ($_SERVER['REQUEST_METHOD'] !== 'POST')
        //bad request
        throw new KBException(-100);
    if (!key_exists('token', $_COOKIE))
        throw new KBException(-10);
    $jwt = jwt_decode($_COOKIE['token']);
    if ($jwt['type'] !== 2)
        throw new KBException(-100);
    if (!key_exists('pid', $_POST) ||
        !preg_match("/^[1-9]\d*$/AD", $_POST['pid']) ||
        !key_exists('file', $_FILES) ||
        !preg_match_all("/\.(xlsx|xls|csv)$/iD", $_FILES['file']['name'], $file_type))
        throw new KBException(-100);
    $pid = (int)$_POST['pid'];
    //检查pid
    $ans = $db->query("SELECT `name`,`total`,`total_only` FROM `project` WHERE `pid`={$pid}");
    if ($ans->num_rows !== 1)
        throw new KBException(-101);
    $res = $ans->fetch_row();
    $name = $res[0];
    $total = (int)$res[1];
    $total_only = (int)$res[2];
    //检查是否所属pid
    $ans = $db->query("SELECT 1 FROM `user-project` WHERE `uid`={$jwt['uid']} AND `pid`={$pid} LIMIT 1");
    if ($ans->num_rows !== 1)
        throw new KBException(-101);
    //拉取group
    $ans = $db->query("SELECT `gid` FROM `pgroup` WHERE `pid`={$pid}");
    $gids = $ans->fetch_all();
    foreach ($gids as &$val) {
        $val = $val[0];
    }
    $ans = $db->query(
        "SELECT `gid` FROM `user-group` WHERE `uid`={$uid} AND `gid` IN (" .
        implode(',', $gids) . ")");
    $gids = $ans->fetch_all();
    foreach ($gids as &$val) {
        $val = $val[0];
    }
    //拉取question
    $ans = $db->query("SELECT `name`,`comment`,`qid` FROM `question` WHERE `pid`={$pid} ORDER BY `pqid`");
    $num_question = $ans->num_rows;
    $questions = $ans->fetch_all();
    //拉取材料
    $ans = $db->query("SELECT `cid` FROM `content-group` WHERE `gid` IN (" .
        implode(',', $gids) . ")");
    $num_contents = $ans->num_rows;
    $cids = $ans->fetch_all();
    foreach ($cids as &$val) {
        $val = $val[0];
    }
    $ans = $db->query("SELECT `cid`,`name` FROM `content` WHERE `cid` IN (" .
        implode(',', $cids) . ")");
    $contents = $ans->fetch_all();
    //解析excel文件
    /**
     * 需要验证的内容有
     * - 标题（A1）
     * - 题目（name=C2:letter($num_question+3)2,comment=C3:letter($num_question+3)3)
     * - 是否可打总分(letter($num_question+4)2)
     * - 材料名称和cid（B4:B($num_contents+3))
     */
    //检测文件格式
    $inputFileType = \PhpOffice\PhpSpreadsheet\IOFactory::identify($_FILES['file']['tmp_name']);
    $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader($inputFileType);
    //读取表格
    $table = $reader->load($_FILES['file']['tmp_name']);
    //检测标题
    if ($name !== $table->getActiveSheet()->getCell('A1'))
        throw new KBException(-115);
    //检测是否可只打总分
    if ($total_only) {
        if ($table->getActiveSheet()->getCell(getLetter($num_question + 4) . '2') !== "合计总分\n（可只打总分）")
            throw new KBException(-115);
    } else {
        if ($table->getActiveSheet()->getCell(getLetter($num_question + 4) . '2') !== "合计总分\n（可只打总分）")
            throw new KBException(-115);
    }
    //检测题目（严格，题目顺序必须按pqid排列），从C2/C3往左
    foreach ($questions as $key => $val) {
        $l = getLetter($key + 3);
        if ($table->getActiveSheet()->getCell("{$l}2") !== $val[0] ||
            $table->getActiveSheet()->getCell("{$l}3") !== $val[1])
            throw new KBException(-115);
    }
    //检测cid（严格，材料顺序必须按cid顺序排列），从B4往下
    foreach ($contents as $key => $val) {
        if ($table->getActiveSheet()->getCell('B' . ($key + 4)) !== $val[1])
            throw new KBException(-115);
        //检测超链接
        if ($table->getActiveSheet()->getCell('B' . ($key + 4))->getHyperlink() !==
            'https://' . DOMAIN . PATH . '#cid=' . $val[0])
            throw new KBException(-115);
    }
    //拉取分数，C4:
    //key1=cid,key2=pqid,val=score
    $scores = [];
    //每个评分的SQL语句
    $scores_sql = [];
    //key为cid，val为分数
    $scores_total_only = [];
    //只打总分的情况
    if ($total_only) {
        $l = getLetter($num_question + 4);
        foreach ($contents as $key => $val) {
            $s = $table->getActiveSheet()->getCell("{$l}4");
            if (!is_numeric($s))
                continue;
            $s = (int)$s;
            //分数超过总分
            if ($s > $total)
                throw new KBException(-115);
            //计入
            $scores_total_only[(int)$val[0]] = $s;
            //合成语句
            $scores_sql[] = "({$val[0]},{$jwt['uid']},0,0,{$s})";
        }
    }
    //横轴扫描，题目
    for ($i = 0; $i < $num_question; $i++) {
        $l = getLetter($i + 3);
        //纵轴扫描，课题
        foreach ($contents as $key => $val) {
            //对于既打总分又打各项的以总分为准，跳过
            if ($scores_total_only[(int)$val[0]] !== null)
                continue;
            $s = $table->getActiveSheet()->getCell($l . ($key + 4));
            //空值
            if ($s === '')
                continue;
            if (!is_numeric($s))
                throw new KBException(-115);
            //计入
            $scores[$val[0]][$i + 1] = (int)$s;
            //合成语句
            $scores_sql[] = "({$val[0]},{$jwt['uid']},{$questions[$i+1][2]},{$i}+1,{$s})";
        }
    }
    //合成sql语句，插入数据库
    $sql = "INSERT INTO `score` (`cid`,`uid`,`qid`,`pqid`,`score`) VALUES " . implode(',', $scores_sql);
    $db->query($sql);
    if ($db->sqlstate !== '00000')
        throw new KBException(-60);
    //响应
    echo json_encode(['status' => 0, 'msg' => '']);

} catch (KBException $e) {
    echo json_encode(['status_code' => $e->getCode(), 'msg' => $e->getMessage()]);
} catch (Exception $e) {
    echo json_encode(['status_code' => -200, 'msg' => 'Unknow error']);
}