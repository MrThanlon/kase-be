<?php
/**
 * Created by PhpStorm.
 * User: hzy
 * Date: 2019/2/17
 * Time: 11:48
 * 评审员下载评分表，表中需要包含已评分数据。
 * 部分与download_empty_table重合
 *
 * Update: 2019/11/17 恢复原来的内容，后台自动生成评分表
 */

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xls;

try {
    require_once __DIR__ . '/../../include/jwt.php';
    require_once __DIR__ . '/../../vendor/autoload.php';
    require_once __DIR__ . '/../../include/letter_sheet.php';
    if ($_SERVER['REQUEST_METHOD'] !== 'GET')
        //bad request
        throw new KBException(-100);
    if (!key_exists('token', $_COOKIE))
        throw new KBException(-10);
    $jwt = jwt_decode($_COOKIE['token']);
    if ($jwt['type'] !== 2)
        throw new KBException(-100);
    //检查pid，拉取名称和总分
    $ans = $db->query("SELECT `total_only`,`name`,`pid` FROM `project` WHERE `pid`=(SELECT `pid` FROM `user-project` WHERE `uid`={$jwt['uid']} LIMIT 1) LIMIT 1");
    if ($ans->num_rows === 0)
        throw new KBException(-101);
    $res = $ans->fetch_row();
    $total_only = $res[0] === '1' ? true : false;
    $name = $res[1];
    $pid = $res[2];
    //拉取question
    $ans = $db->query("SELECT `name`,`comment`,`pqid`,`max` FROM `question` WHERE `pid`={$pid} ORDER BY `pqid`");
    $num_question = $ans->num_rows;
    $questions = $ans->fetch_all();
    //拉取contents
    //先拉取所有分组所在cid
    $cids = [];
    $ans = $db->query("SELECT `cid` FROM `content-group` WHERE `gid` IN (SELECT `gid` FROM `user-group` WHERE `uid`={$jwt['uid']})");
    $num_content = $ans->num_rows;
    $res = $ans->fetch_all();
    foreach ($res as $val) {
        $cids[] = (int)$val[0];
    }
    $cids = array_unique($cids);
    // FIXME: 这里可以合并为一条语句
    $ans = $db->query("SELECT `name`,`cid` FROM `content` WHERE `cid` IN (" . implode(',', $cids) . ") ORDER BY `cid`");
    $contents = $ans->fetch_all();
    //生成表格
    $excel = new Spreadsheet();
    //设置文件属性
    $excel->getProperties()->setCreator("Kase");
    $excel->getProperties()->setLastModifiedBy("Kase");
    $excel->getProperties()->setTitle($name);
    $excel->getProperties()->setSubject($name);
    $excel->getProperties()->setDescription("Evaluate Form Created By Kase");
    $excel->getProperties()->setKeywords("evaluate kase");
    $excel->getProperties()->setCategory("file");
    //添加数据
    //设置第一个sheet为正在活动的sheet，PHPExcel自带一个sheet所以我们不需要创建sheet
    $excel->setActiveSheetIndex(0);
    //合并单元格处理
    //计算总列数
    $num_cols = $num_question + 3;
    //进制转换
    $letter = getLetter($num_cols);
    //其他的合并
    $excel->getActiveSheet()->mergeCells("A1:{$letter}1");
    $excel->getActiveSheet()->mergeCells('A2:A3');
    $excel->getActiveSheet()->mergeCells('B2:B3');
    $excel->getActiveSheet()->mergeCells("{$letter}2:{$letter}3");
    //设置指定单元格数据
    $excel->getActiveSheet()->setCellValue('A1', $name);
    $excel->getActiveSheet()->setCellValue('A2', "序号");
    $excel->getActiveSheet()->setCellValue('B2', "课题名称");
    $excel->getActiveSheet()->setCellValue("{$letter}2", $total_only ?"合计总分\r（可只打总分）":"合计总分\r（不可只打总分）");
    //填充序号、课题、超链接以及分数合计公式
    $rletter = getLetter($num_cols - 1); //右侧倒数第二个字母
    foreach ($contents as $key => $val) {
        $current_row = $key + 4;
        $excel->getActiveSheet()->setCellValue('A' . $current_row, $key + 1); //序号
        $excel->getActiveSheet()->setCellValue('B' . $current_row, $val[0]); //课题
        //超链接
        // FIXME: 链接地址可能要修改
        $excel->getActiveSheet()
            ->getCell('B' . $current_row)
            ->getHyperlink()
            ->setUrl(FRONTEND . '#/judger/' . $val[1]);
        //公式
        $excel->getActiveSheet()->setCellValue(
            $letter . $current_row, "=SUM(C{$current_row}:{$rletter}{$current_row})"
        );
    }
    //填充题目
    foreach ($questions as $key => $val) {
        $l = getLetter($key + 3);
        $excel->getActiveSheet()->setCellValue("{$l}2", "{$val[0]}（{$val[3]}分）");
        $excel->getActiveSheet()->setCellValue("{$l}3", $val[1]);
    }
    //文件名编码
    $nameUrlencode = urlencode($name);
    //启动文件下载
    $writer = new Xls($excel);
    header('Content-Type: application/vnd.ms-excel; charset=utf-8');
    header("Content-Disposition: attachment; filename=\"{$name}.xls\"; filename*=UTF-8''{$nameUrlencode}.xls");
    $writer->save("php://output");

} catch (KBException $e) {
    //echo json_encode(['status' => $e->getCode(), 'msg' => $e->getMessage()]);
} catch (Exception $e) {
    //echo json_encode(['status' => -200, 'msg' => 'Unknow error']);
}