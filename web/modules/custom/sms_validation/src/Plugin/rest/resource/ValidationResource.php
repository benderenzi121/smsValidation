<?php
namespace Drupal\sms_validation\Plugin\rest\resource;

use Drupal\rest\Plugin\ResourceBase;
use Drupal\rest\ResourceResponse;
use Tobscure\JsonApi\Collection;
use Tobscure\JsonApi\Document;
use Tobscure\JsonApi\ErrorHandler;
use Tobscure\JsonApi\Resource;

/**
 * Provides a Demo Resource
 *
 * @RestResource(
 *   id = "validation_resource",
 *   label = @Translation("Validation Resource"),
 *   uri_paths = {
 *     "canonical" = "/v1/smsValidation/{phoneNumber}"
 *   }
 * )
 */

class ValidationResource extends ResourceBase
{
    /**
     * Responds to entity GET requests.
     * 
     * @return \Drupal\rest\ResourceResponse
     */
    public function get($phoneNumber) 
    {
        $twilio_connector_service = \Drupal::Service(id: 'twilio.api_connector');
        $verifyNumber = $twilio_connector_service->verifyNumber($phoneNumber);
       
        if (!empty($verifyNumber)) {
            $phone_number_data = [
                'valid' => $verifyNumber->valid,
                'countryCode' => $verifyNumber->countryCode,
              ];
              
            if ($phone_number_data['valid'] == false) {
                $non_valid_error = [
                  'errors' => [
                    "status" => '400',
                    "title" => 'Invalid Phone Number',
                    "detail" => 'the provided number is not recognized as a valid phone number',
                  ],
                ];
                return new ResourceResponse($non_valid_error, 400);
            } elseif ($phone_number_data['countryCode'] != 'US') {
                $non_us_error = [
                  'errors' => [
                    "status" => '400',
                    "title" => 'Invalid Phone Number',
                    "detail" => 'the provided number must must reside in the US',
                  ],
                ];
                return new ResourceResponse($non_us_error, 400);
            }
            if ($phone_number_data['valid'] == true && $phone_number_data['countryCode'] == 'US') {

                // Calls to the verify SMS function of the twilio service.
                // this is a paid request that Looks for mobile carrier information and returns it.
                $result = $twilio_connector_service->verifySms($phoneNumber);
                if ($result === 'mobile') {
                    // Good response for mobile numbers.
                    return new ResourceResponse(202);
                } else {
                    // Bad response for no mobile numbers.
                    $non_mobile_error = [
                    'errors' => [
                      "status" => '400',
                      "title" => 'Invalid Phone Number',
                      "detail" => 'the provided number must be a valid mobile number to recieve sms',
                    ],
                    ];
                     return new ResourceResponse($non_mobile_error, 500);
                }
            } else {
                // We shouldnt have ever made it here (planning on removing)
                return new ResourceResponse('501');
            }
            
        }
        return new ResourceResponse($verifyNumber);
    }
}
