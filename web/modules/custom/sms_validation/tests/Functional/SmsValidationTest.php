<?php

namespace Drupal\Tests\sms_validation\Functional;

use Drupal\Tests\BrowserTestBase;

/**
 * Tests the SMS validation resource endpoint.
 *
 * @group sms_validation
 */
class SmsValidationTest extends BrowserTestBase {
    // dump('test');

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
//   protected static $modules = ['sms_validation'];

  /**
   * Set to TRUE to strict check all configuration saved.
   *
   * @var bool
   * @see \Drupal\Core\Config\Testing\ConfigSchemaChecker
   */
  protected $strictConfigSchema = FALSE;

//   public function setUp() {
//     parent::setUp();
//     $this->http = $this->getHttpClient();
//     $this->baseUrl = $_ENV['SIMPLETEST_BASE_URL'];

//     // Add a test account.
//     $this->account = $this->drupalCreateUser([
//       'restful get sms_validation',
//     ], 'testUser');
//   }

  /**
   * Test the validation resource endpoint with a valid number.
   */
  public function testValidNumber() {
    dump('test');
    // Send a GET request to the validation resource endpoint with a valid number.
    $phoneNumber = '1234567890';
  
    // $this->http->request('/v1/smsValidation/' . $phoneNumber, ['Accept' => 'application/vnd.api+json']);

    // Verify that the response is valid and has the expected status code and body.
    // $this->assertResponse(202);
    // $this->assertHeader('Content-Type', 'application/vnd.api+json; charset=UTF-8');
    // $this->assertJson('{"data":[]}');
  }

  /**
   * Test the validation resource endpoint with an invalid number.
   */

}
