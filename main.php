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
    $date = get_the_date('Y-m-d', $post_id);

    $client = new Google_Client();
    $client->setAuthConfig(__DIR__ . '/service-account.json');
    $client->addScope(Google_Service_Calendar::CALENDAR_EVENTS);

    $service = new Google_Service_Calendar($client);
    $description = $content;

    $event = new Google_Service_Calendar_Event([
        'summary' => $title,
        'description' => $description,
        'start' => ['date' => $date, 'timeZone' => 'Asia/Taipei'],
        'end' => ['date' => date('Y-m-d', strtotime($date . ' +1 day')), 'timeZone' => 'Asia/Taipei']
    ]);
    custom_log("+insert event");
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
}

function custom_log($message)
{
    $log_file = __DIR__ . '/debug.log';
    file_put_contents($log_file, date("[Y-m-d H:i:s] ") . $message . PHP_EOL, FILE_APPEND);
}

add_action('publish_post', 'google_calendar_add_event');

?>
