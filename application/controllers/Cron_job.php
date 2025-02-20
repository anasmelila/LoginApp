<?php
defined('BASEPATH') OR exit('No direct script access allowed');

use Google\Client as GoogleClient;
use Google\Service\Calendar;
use Twilio\Rest\Client as TwilioClient;

class Cron_job extends CI_Controller {

	private $twilio_sid = "your-twilio-sid";
	private $twilio_token = "your-twilio-token";
	private $twilio_from_number = "your-twilio-phone-number";

    public function __construct() {
        parent::__construct();
        $this->load->database();
        $this->load->model('user_model');
        $this->load->library('session');
        date_default_timezone_set('Asia/Kolkata');
    }

	public function check_calendar_events() {
		log_message('info', "Cron job started: Checking upcoming events.");
	
		// Get all users with Google access tokens
		$users = $this->user_model->get_all_users();
		
		if (empty($users)) {
			log_message('error', "No users found with Google access tokens.");
			echo "No users found.\n";
			return;
		}
	
		foreach ($users as $user) {
			echo " Checking events for user: {$user->email}\n";
			log_message('info', "Checking events for user: {$user->email}");
		
			$events = $this->get_upcoming_events($user->google_access_token);
			
			if (!empty($events)) {
				foreach ($events as $event) {
					$eventSummary = $event->getSummary();
					$eventStart = $event->getStart()->getDateTime() ?: $event->getStart()->getDate();
		
					echo "Calling user: {$user->phone} for event: $eventSummary at $eventStart\n";
					log_message('info', "Calling user: {$user->phone} for event: $eventSummary at $eventStart");
		
					// ✅ Pass all 3 required parameters
					$this->trigger_twilio_call($user->phone, $eventSummary, $eventStart);
				}
			} else {
				echo "No upcoming events for {$user->email}\n";
				log_message('info', "❌ No upcoming events for {$user->email}");
			}
		}
	
		log_message('info', "✅ Cron job completed.");
		echo "✅ Cron job completed.\n";
	}
	

    private function get_upcoming_events($access_token) {
		log_message('info', "📡 Fetching Google Calendar events...");
	
		$client = new GoogleClient();
		$client->setAccessToken($access_token);
	
		if ($client->isAccessTokenExpired()) {
			log_message('error', "❌ Google Access Token Expired.");
			echo "Error: Google Access Token Expired.\n";
			return [];
		}
	
		$service = new Calendar($client);
		$calendarId = 'primary';
	
		date_default_timezone_set('Asia/Kolkata');
		$now = new DateTime('now', new DateTimeZone('Asia/Kolkata'));
		$later = clone $now;
		$later->modify('+5 minutes');
	
		$nowUtc = $now->setTimezone(new DateTimeZone('UTC'))->format('c');
		$laterUtc = $later->setTimezone(new DateTimeZone('UTC'))->format('c');
	
		log_message('info', "⏰ Checking events between $nowUtc and $laterUtc");
	
		$optParams = [
			'maxResults' => 5,
			'singleEvents' => true,
			'orderBy' => 'startTime',
			'timeMin' => $nowUtc,
			'timeMax' => $laterUtc,
		];
	
		try {
			$events = $service->events->listEvents($calendarId, $optParams);
			log_message('info', "📅 Found " . count($events->getItems()) . " events.");
			
			foreach ($events->getItems() as $event) {
				log_message('info', "📌 Event: " . $event->getSummary());
			}
			
			return $events->getItems();
		} catch (Exception $e) {
			log_message('error', "❌ Error fetching events: " . $e->getMessage());
			echo "Error: " . $e->getMessage();
			return [];
		}
	}
	

    private function trigger_twilio_call($phoneNumber, $eventSummary, $eventStart) {
        try {
            $client = new TwilioClient($this->twilio_sid, $this->twilio_token);
            $message = "Reminder: Your event '$eventSummary' is scheduled at $eventStart.";

            $call = $client->calls->create(
                $phoneNumber,
                $this->twilio_from_number,
                ["twiml" => "<Response><Say>$message</Say></Response>"]
            );

            log_message('info', "Twilio call placed to $phoneNumber for event: $eventSummary");
        } catch (Exception $e) {
            log_message('error', "Twilio call failed for $phoneNumber. Error: " . $e->getMessage());
        }
    }
}
