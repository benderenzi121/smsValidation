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
    $sid   = Settings::get('twilio_sid');
    $token = Settings::get('twilio_token');

    $this->twilioClient = new Client($sid, $token);
  }

  /**
   * Our Verify Number function hits the twilio API to lookup a phone number.
   *
   * @param string $phone_number
   *   Telephone Number to check.
   * @param bool $isValid
   *   Flag to check whether number was already checked for validity.
   */
  public function verifyNumber($phone_number, $isValid = FALSE) {
    // If the validity has not been checked: we check it here.
    try {
      if ($isValid == FALSE) {
        $validation = $this->twilioClient->lookups->v2->phoneNumbers($phone_number)->fetch();
        return $validation;
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
      throw $e;
    }
  }

}
