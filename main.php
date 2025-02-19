<?php
/**
 * Plugin Name: Simple Auto Google Event
 * Description: Automatically adds a Google Calendar event when a post is published.
 * Version: 1.0
 * Author:Daniel 
 */

if (!defined('ABSPATH')) {
    exit;
}

require_once __DIR__ . '/google-api/vendor/autoload.php';

function google_calendar_add_event($post_id)
{
    custom_log("Debugging started...");
    if (get_post_meta($post_id, '_google_event_created', true)) {
        return;
    }

    $post = get_post($post_id);
    if (!$post || $post->post_status !== 'publish') {
        return;
    }

    $title = $post->post_title;
    $content = wp_strip_all_tags($post->post_content);
    custom_log("Debugging get the date...");
    $date = extract_event_date($content);
    if (!$date) {
        custom_log("Using post date instead.");
        $date = get_the_date('Y-m-d', $post_id);
        $title = "時間待確認" . $title;
    }

    custom_log("get_the_date:". $date);
    $client = new Google_Client();
    $client->setAuthConfig(__DIR__ . '/service-account.json');
    $client->addScope(Google_Service_Calendar::CALENDAR_EVENTS);

    $service = new Google_Service_Calendar($client);
    custom_log("new Google_Service_Calendar");
    custom_log($title);
    custom_log(substr($content, 0, 500));
    custom_log( $date);
    custom_log( date('Y-m-d', strtotime($date . ' +1 day')));
    $post_url = get_permalink($post_id);
    $content = normalize_string($post_url . "\n\n" . $content, $date);
    $description = substr($content, 0, 5000);

    $event = new Google_Service_Calendar_Event([
        'summary' => $title,
        'description' => $description,
        'start' => ['date' => $date, 'timeZone' => 'Asia/Taipei'],
        'end' => ['date' => date('Y-m-d', strtotime($date . ' +1 day')), 'timeZone' => 'Asia/Taipei']
    ]);
    custom_log("+insert event");
    $file = __DIR__ . '/output.bin'; // Output binary file
    file_put_contents($file, $description);
    $calendarId = 'dennytpe@gmail.com';
    try {
        $event = $service->events->insert($calendarId, $event);
    }
    catch (Exception $e) {
        print $e -> getMessage();
	custom_log($e -> getMessage());
    }
    custom_log("done");
    #$event = $service->events->insert($calendarId, $event);

    update_post_meta($post_id, '_google_event_created', true);
    custom_log("event3:...");
}

function custom_log($message)
{
    $log_file = __DIR__ . '/debug.log';
    file_put_contents($log_file, date("[Y-m-d H:i:s] ") . $message . PHP_EOL, FILE_APPEND);
}
function normalize_string($text)
{
    custom_log("detect_encoding" . detect_encoding($text));
    // Remove non-printable characters manually
    #$text = preg_replace('/[\x00-\x1F\x7F]/u', ' ', $text); // Control characters
    #$text = preg_replace('/\s+/u', ' ', $text); // Extra spaces
    #$text = preg_replace('/時間.*/', '2025-3', $text);
    return trim($text);
}
function detect_encoding($string) {
    if (preg_match('//u', $string)) {
        return 'UTF-8';
    }
    return 'ISO-8859-1';
}

function extract_event_date($content)
{
    custom_log("Extracting date from content...");

    // Match Chinese date pattern: "時間：一百一十四年三月十五日"
    if (preg_match('/時間：([一二三四五六七八九十百零]+)年([一二三四五六七八九十]+)月([一二三四五六七八九十]+)日/', $content, $matches)) {

        $minguo_year = chinese_to_number(trim($matches[1])); // Convert to number

        $year = $minguo_year + 1911; // Convert Minguo year to Gregorian
        $month = chinese_to_number(trim($matches[2]));
        $day = chinese_to_number(trim($matches[3]));

        $date = sprintf('%04d-%02d-%02d', $year, $month, $day);
        return $date;
    }

    custom_log("❌ No valid date found in content.");
    return null;
}

function chinese_to_number($chinese)
{
    $map = [
        '零' => 0, '一' => 1, '二' => 2, '三' => 3, '四' => 4,
        '五' => 5, '六' => 6, '七' => 7, '八' => 8, '九' => 9,
        '十' => 10, '百' => 100
    ];
    
    $number = 0;
    $temp = 0;
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
    }
    return $number + $temp;
}
add_action('publish_post', 'google_calendar_add_event');

?>
