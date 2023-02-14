<?php

namespace Drupal\sms_validation\Service;

use Twilio\Exceptions\TwilioException;
use Twilio\Rest\Client;
use Drupal\Core\Site\Settings;
use GuzzleHttp\Exception\RequestException;

/**
 * Twilio connector class.
 */
class TwilioAPIConnector {
  /**
   * Twilio  client variable.
   *
   * @var Twilio\Rest\Client
   */
  protected $twilioClient;

  /**
   * Constructor function that creats an instance of our twilio client.
   */
  public function __construct() {
    // Test API does not send out SMS.
    if (Settings::get('environment') === 'dev') {
      $sid   = Settings::get('test_twilio_sid');
      $token = Settings::get('test_twilio_token');
    }
    else {
      $sid   = Settings::get('twilio_sid');
      $token = Settings::get('twilio_token');
    }

    $this->twilioClient = new Client($sid, $token);
  }

  /**
   * Our Verify Number function hits the twilio API to lookup a phone number.
   *
   * @param string $phone_number
   *   Telephone Number to check.
   * @param string $isValid
   *   Flag to check whether number was already checked for validity.
   */
  public function verifyNumber($phone_number, $isValid) {
    // If the validity has not been checked: we check it here.
    try {
      if ($isValid == FALSE) {
        $data = [];
        $validation = $this->twilioClient->lookups->v2->phoneNumbers($phone_number)->fetch();
        $data = $validation;
        return $data;
      }
      else {
        $validation = $this->twilioClient->lookups->v1->phoneNumbers($phone_number)->fetch(["type" => ["carrier"]]);
        return $validation->carrier['type'];
      }

    }
    catch (TwilioException $e) {
      throw $e;
    }
    catch (RequestException $e) {
      \Drupal::logger($e);
    }

  }

}
