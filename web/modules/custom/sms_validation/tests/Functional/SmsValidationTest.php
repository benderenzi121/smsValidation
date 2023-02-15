<?php

namespace Drupal\Tests\sms_validation\Functional;

use Drupal\Tests\BrowserTestBase;

/**
 * Tests the SMS validation resource endpoint.
 *
 * @group sms_validation
 */
class SmsValidationTest extends BrowserTestBase {

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'seven';

  /**
   * Reference to http client for virtual session.
   *
   * @var [type]
   */
  protected $http;

  /**
   * {@inheritdoc}
   */
  protected static $modules = ['sms_validation'];

  /**
   * Set to TRUE to strict check all configuration saved.
   *
   * @var bool
   * @see \Drupal\Core\Config\Testing\ConfigSchemaChecker
   */
  protected $strictConfigSchema = FALSE;

  /**
   * Setup Function.
   */
  public function setUp() {
    parent::setUp();
    $this->http = $this->getHttpClient();
    $this->baseUrl = $_ENV['SIMPLETEST_BASE_URL'];

    // Add a test account.
    $this->account = $this->drupalCreateUser([
      'restful get sms_validation',
    ], 'testUser');
  }

  /**
   * Hit the validation resource endpoint with a valid number.
   */
  public function testValidNumber() {
    // Send a GET request to the validation resource endpoint with a valid number.
    $phoneNumber = '3478459201';
    $this->http->request('/v1/smsValidation/' . $phoneNumber, ['Accept' => 'application/vnd.api+json']);

    // Verify that the response is valid and has the expected status code and body.
    $this->assertResponse(202);
    $this->assertHeader('Content-Type', 'application/vnd.api+json; charset=UTF-8');
    $this->assertJson('');
  }

  /**
   * Hit the validation resource endpoint with an invalid number (< 10 digits).
   */
  public function testInvalidNumberShort() {
    // Send a GET request to the validation resource endpoint with a <10 digit #.
    $phoneNumber = '347845920';
    $this->http->request('get', '/v1/smsValidation/' . $phoneNumber, ['Accept' => 'application/vnd.api+json']);

    // Verify that the response is valid and has the expected status code and body.
    $this->assertResponse(400);
    $this->assertHeader('Content-Type', 'application/vnd.api+json; charset=UTF-8');
    $this->assertJson('{
        "errors": {
            "status": "400",
            "title": "Invalid Phone Number",
            "detail": "The provided number must contain exactly 10 digits that are all numbers"
        }
    }');
  }

  /**
   * Hit the validation resource endpoint with an invalid number ( >10 digits).
   */
  public function testInvalidNumberLong() {
    // Send a GET request to the validation resource endpoint with a 11 digit #.
    $phoneNumber = '34784592011';
    $this->http->request('get', '/v1/smsValidation/' . $phoneNumber, ['Accept' => 'application/vnd.api+json']);

    // Verify that the response is valid and has the expected status code and body.
    $this->assertResponse(400);
    $this->assertHeader('Content-Type', 'application/vnd.api+json; charset=UTF-8');
    $this->assertJson('{
        "errors": {
            "status": "400",
            "title": "Invalid Phone Number",
            "detail": "The provided number must contain exactly 10 digits that are all numbers"
        }
    }');
  }

  /**
   * Hit validation resource endpoint with an invalid number (Not a valid phone number).
   */
  public function testInvalidNumber() {
    // Send a GET request to the validation resource endpoint with a valid number.
    $phoneNumber = '1234568793';
    $this->http->request('get', '/v1/smsValidation/' . $phoneNumber, ['Accept' => 'application/vnd.api+json']);

    // Verify that the response is valid and has the expected status code and body.
    $this->assertResponse(400);
    $this->assertHeader('Content-Type', 'application/vnd.api+json; charset=UTF-8');
    $this->assertJson('{
        "errors": [
            {
                "status": "400",
                "title": "Invalid Phone Number",
                "detail": "The provided number is not recognized as a valid phone number"
            }
        ]
    }');
  }

  /**
   * Hit validation resource endpoint with an invalid number (Non Us Number).
   */
  public function testInvalidNumberNonUs() {
    // Send a GET request to the validation resource endpoint with a valid number.
    $phoneNumber = '4165550199';
    $this->http->request('get', '/v1/smsValidation/' . $phoneNumber, ['Accept' => 'application/vnd.api+json']);

    // Verify that the response is valid and has the expected status code and body.
    $this->assertResponse(400);
    $this->assertHeader('Content-Type', 'application/vnd.api+json; charset=UTF-8');
    $this->assertJson('{
        "errors": [
            {
                "status": "400",
                "title": "Invalid Phone Number",
                "detail": "The provided number must must reside in the US"
            }
        ]
    }');
  }

  /**
   * Hit validation resource endpoint with an invalid number (landline).
   */
  public function testInvalidNumberNonMobile() {
    // Send a GET request to the validation resource endpoint with a valid number.
    $phoneNumber = '2125551212';
    $this->http->request('get', '/v1/smsValidation/' . $phoneNumber, ['Accept' => 'application/vnd.api+json']);

    // Verify that the response is valid and has the expected status code and body.
    $this->assertResponse(400);
    $this->assertHeader('Content-Type', 'application/vnd.api+json; charset=UTF-8');
    $this->assertJson('{
        "errors": {
            "status": "400",
            "title": "Invalid Phone Number",
            "detail": "The provided number must be a valid mobile number to recieve sms"
        }
    }');
  }

}
