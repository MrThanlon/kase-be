<?php
/**
 * Created by PhpStorm.
 * User: ziyihuang
 * Date: 2019-03-07
 * Time: 22:52
 * 就是把数字转换成Excel表格中的字母而已
 */

define('LETTER_SHEET', ['', 'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z']);
/**
 * @param int $cols
 * @return string
 */
function getLetter(int $cols)
{
    return LETTER_SHEET[$cols / 26] . LETTER_SHEET[$cols % 26];
}