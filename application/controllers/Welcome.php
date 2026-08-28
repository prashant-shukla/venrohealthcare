<?php
defined('BASEPATH') OR exit('No direct script access allowed');
use Twilio\Rest\Client;

class Welcome extends CI_Controller {

    public function index()
    {
        // Demo/scaffold controller. Not the application's SMS pipeline (see the "sms" module).
        show_404();
    }

    protected function sendSMS($data) {
        // Credentials must come from the environment, never hardcoded in source control.
        $sid   = getenv('TWILIO_ACCOUNT_SID');
        $token = getenv('TWILIO_AUTH_TOKEN');
        $from  = getenv('TWILIO_FROM_NUMBER');

        if (empty($sid) || empty($token) || empty($from)) {
            return 'Twilio credentials are not configured.';
        }

        $client = new Client($sid, $token);

        return $client->messages->create(
            $data['phone'],
            array(
                'from' => $from,
                'body' => $data['text'],
            )
        );
    }
}
