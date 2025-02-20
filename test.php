<?php
require_once 'google-api/vendor/autoload.php'; // Update path if necessary

use Google\Client;
use Google\Service\Calendar;
use Google\Service\Calendar\Event;


// Path to your service account JSON file (upload it via FTP)
$serviceAccountPath = __DIR__ . '/service-account.json'; // **********Adjust path as needed

$client = new Google_Client();
$client->setAuthConfig($serviceAccountPath);
$client->setScopes(Google_Service_Calendar::CALENDAR);
$client->setScopes([
    'https://www.googleapis.com/auth/calendar' // Full calendar access
]);
$client->setAccessType('offline');
#$client->setSubject('yourname@team.com'); // Use an email with access to the calendar

$service = new Google_Service_Calendar($client);
$date = "2025-2-20";
$event = new Google_Service_Calendar_Event([
    'summary'     => 'Team Meeting',
    'location'    => 'Online',
    'description' =>  'Weeekly sync',
    'start' => ['date' => $date, 'timeZone' => 'Asia/Taipei'],
    'end' => ['date' => date('Y-m-d', strtotime($date . ' +1 day')), 'timeZone' => 'Asia/Taipei']
]);


// ****** the calendarID my be <yourmail>@gamil.com or <some text>@group.calendar.google.com
$calendarId = 'yourmail@outlook.com'; // ****************Or use a specific calendar ID

$event = $service->events->insert($calendarId, $event);

echo "Event created: " . $event->htmlLink;
?>
