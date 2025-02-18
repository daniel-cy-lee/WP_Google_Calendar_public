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
    if (get_post_meta($post_id, '_google_event_created', true)) {
        return;
    }

    $post = get_post($post_id);
    if (!$post || $post->post_status !== 'publish') {
        return;
    }

    $title = $post->post_title;
    $content = wp_strip_all_tags($post->post_content);
    $date = get_the_date('Y-m-d\TH:i:s', $post_id);

    $client = new Google_Client();
    $client->setAuthConfig(__DIR__ . '/service-account.json');
    $client->addScope(Google_Service_Calendar::CALENDAR_EVENTS);

    $service = new Google_Service_Calendar($client);
    $event = new Google_Service_Calendar_Event([
        'summary' => $title,
        'description' => substr($content, 0, 500),
        'start' => ['dateTime' => $date, 'timeZone' => 'UTC'],
        'end' => ['dateTime' => date('Y-m-d\TH:i:s', strtotime($date . ' +1 hour')), 'timeZone' => 'UTC']
    ]);

    $calendarId = 'dennytpe@gmail.com';
    $event = $service->events->insert($calendarId, $event);

    update_post_meta($post_id, '_google_event_created', true);
}


add_action('publish_post', 'google_calendar_add_event');

?>
