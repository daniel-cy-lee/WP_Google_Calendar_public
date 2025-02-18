<?php
echo(chinese_to_number("一百一十四"));
function chinese_to_number($chinese)
{
    $map = [
        '零' => 0, '一' => 1, '二' => 2, '三' => 3, '四' => 4,
        '五' => 5, '六' => 6, '七' => 7, '八' => 8, '九' => 9,
        '十' => 10, '百' => 100
    ];

    $number = 0;
    $temp = 0;
    custom_log(normalize_string("chinese_to_number...") . $chinese);
    foreach (preg_split('//u', $chinese, -1, PREG_SPLIT_NO_EMPTY) as $char) {
        if ($char == '十') {
            $temp = $temp == 0 ? 10 : $temp * 10;
        } elseif ($char == '百') {
            $temp *= 100;
        } else {
            $temp += $map[$char];
        }
        if ($temp >= 10) {
            $number += $temp;
            $temp = 0;
        }
        custom_log("itemp..." . $temp);
    }
    return $number + $temp;
}
function mytrim_proc($text)
{
    return trim(preg_replace('/\s+/', ' ', $text));
}
function normalize_string($text)
{
    // Remove non-printable characters manually
    $text = preg_replace('/[\x00-\x1F\x7F]/u', ' ', $text); // Control characters
    $text = preg_replace('/\s+/u', ' ', $text); // Extra spaces
    return trim($text);
}

function custom_log($message)
{
    $log_file = __DIR__ . '/debug.log';
    file_put_contents($log_file, date("[Y-m-d H:i:s] ") . $message . PHP_EOL, FILE_APPEND);
}


?>
