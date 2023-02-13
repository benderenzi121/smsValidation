<?php

namespace Drupal\sms_validation\Service;

use Twilio\Exceptions\TwilioException;
use Twilio\Rest\Client;
use Drupal\Core\Site\Settings;
use Drupal\core\http\ClientFactory;
use GuzzleHttp\Exception\RequestException;
use Drupal;

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
  public function __construct(ClientFactory $client) {
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
   */
  public function verifyNumber($phone_number) {
    // Initialize an empty array that our data will eventually go into.
    $data = [];

    // Try to hit the twilio endpoint with the phone number provided.
    try {
      $validation = $this->twilioClient->lookups->v2->phoneNumbers($phone_number)->fetch();
      $data = $validation;
    }
    catch (TwilioException $e) {
      throw $e;
    }
    catch (RequestException $e) {
      Drupal::logger('twilio', $e, $e->getMessage());
    }
    return $data;
  }

  /**
   * VerifySms is used to verify that the number is a valid mobile number.
   *
   * @param string $phone_number
   *   Phone Number to verify.
   */
  public function verifySms($phone_number) {
    try {
      $validation = $this->twilioClient->lookups->v1->phoneNumbers($phone_number)->fetch(["type" => ["carrier"]]);
    }
    catch (TwilioException $e) {
      throw $e;
    }
    catch (RequestException $e) {
      Drupal::logger('twilio', $e, $e->getMessage());
    }
    return $validation->carrier['type'];
  }

}
