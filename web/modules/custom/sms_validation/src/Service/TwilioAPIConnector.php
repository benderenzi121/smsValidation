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
   * Twilio  client variable;.
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
      $sid   = 'AC91121581a65a14958ee7521f8576a3a5';
      $token = 'a2d2af4a805e6994bf232f7dbc081def';
    }
    else {
      $sid   = 'AC91121581a65a14958ee7521f8576a3a5';
      $token = 'a2d2af4a805e6994bf232f7dbc081def';
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
    $data = [];
    // If the validity has not been checked: we check it here.
    if ($isValid == FALSE) {
      try {
        $validation = $this->twilioClient->lookups->v2->phoneNumbers($phone_number)->fetch();
        $data = $validation;
      }
      catch (TwilioException $e) {
        throw $e;
      }
      catch (RequestException $e) {
        \Drupal::logger($e);
      }
      return $data;
    }
    // If Number is already confirmed valid, we check for SMS capabilities here.
    else {
      try {
        $validation = $this->twilioClient->lookups->v1->phoneNumbers($phone_number)->fetch(["type" => ["carrier"]]);
      }
      catch (TwilioException $e) {
        throw $e;
      }
      catch (RequestException $e) {
        \Drupal::logger($e);
      }
      return $validation->carrier['type'];
    }
  }

}
