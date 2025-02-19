<?php
require_once 'google-api/vendor/autoload.php'; // Update path if necessary

use Google\Client;
use Google\Service\Calendar;
use Google\Service\Calendar\Event;


// Path to your service account JSON file (upload it via FTP)
$serviceAccountPath = __DIR__ . '/service-account.json'; // Adjust path as needed

$client = new Google_Client();
$client->setAuthConfig($serviceAccountPath);
$client->setScopes(Google_Service_Calendar::CALENDAR);
$client->setScopes([
    'https://www.googleapis.com/auth/calendar' // Full calendar access
]);
$client->setAccessType('offline');
#$client->setSubject('dennytpe@gmail.com'); // Use an email with access to the calendar

$service = new Google_Service_Calendar($client);
$date = "2025-2-15";
$file = 'output.bin';
$file2 = 'output.tmp';
// Read back from binary file
$binaryData = file_get_contents($file);
$binaryData = mb_convert_encoding($binaryData, 'UTF-8', 'auto');
$binaryData = substr($binaryData, 0, 86);
$binaryData = normalize_string($binaryData);
file_put_contents($file2, $binaryData);
echo "Read from file: " . $binaryData;
echo "\n\n";
$event = new Google_Service_Calendar_Event([
    'summary'     => 'Team Meeting',
    'location'    => 'Online',
    'description' =>  $binaryData,
    'start' => ['date' => $date, 'timeZone' => 'Asia/Taipei'],
    'end' => ['date' => date('Y-m-d', strtotime($date . ' +1 day')), 'timeZone' => 'Asia/Taipei']
]);
echo( $event['end']['date'] . '*');
echo( $event['start']['date'] . '*');


#$calendarId = 'landscape.mt@gmail.com'; // Or use a specific calendar ID
$calendarId = 'dennytpe@gmail.com'; // Or use a specific calendar ID
$event = $service->events->insert($calendarId, $event);

echo "Event created: " . $event->htmlLink;
function normalize_string($text)
{
    // Remove non-printable characters e8 manually
    $binaryString = "Hello World";
    $binaryString = "\xE8Hello\xE8World";
    $string = "Hèllo, this is a tèst string!"; // Contains "è" (E8 in hex)
    #$text = $binaryString;
    echo "detect_encoding" . detect_encoding($text);
    // Display input string
    echo "string in: " . $text . PHP_EOL;
    // $text = preg_replace('/\xE8/', '', $text); // Control characters
    $text = preg_replace('/\xC3\xA8/u', ' ', $text);
    // Display output string
    echo "string out: " . $text . PHP_EOL; 
    return trim($text);
}
function detect_encoding($string) {
    if (preg_match('//u', $string)) {
        return 'UTF-8';
    }
    return 'ISO-8859-1';
}
?>
