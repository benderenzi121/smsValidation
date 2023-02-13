<?php

namespace Drupal\sms_validation\Plugin\rest\resource;

use Drupal\rest\Plugin\ResourceBase;
use Drupal\rest\ResourceResponse;
use Drupal\sms_validation\Service\TwilioAPIConnector;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Tobscure\JsonApi\Document;

/**
 * Provides a Demo Resource.
 *
 * @RestResource(
 *   id = "validation_resource",
 *   label = @Translation("Validation Resource"),
 *   uri_paths = {
 *     "canonical" = "/v1/smsValidation/{phoneNumber}"
 *   }
 * )
 */
class ValidationResource extends ResourceBase {
  /**
   * Twilio Service variable.
   *
   * @var \Drupal\sms_validation\Service\TwilioAPIConnector
   */
  protected $twilioService;

  /**
   * Construct function.
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    $serializer_formats,
    LoggerInterface $logger,
    TwilioAPIConnector $twilio_service,
    ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $serializer_formats, $logger);
    $this->twilioService = $twilio_service;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->getParameter('serializer.formats'),
      $container->get('logger.factory')->get('rest'),
      $container->get('twilio.api_connector'),
    );
  }

  /**
   * Responds to entity GET requests.
   *
   * @return \Drupal\rest\ResourceResponse
   *   Returns a response based on whether the number is valid.
   */
  public function get($phoneNumber) {
    // Initialize our Twilio service object.
    $twilio_connector_service = \Drupal::Service(id: 'twilio.api_connector');
    // Makes a request through twilio SDK.
    // Will return an empty array if the request could not be made.
    $verifyNumber = $twilio_connector_service->verifyNumber($phoneNumber);

    if (!empty($verifyNumber)) {
      $phone_number_data = [
        'valid' => $verifyNumber->valid,
        'countryCode' => $verifyNumber->countryCode,
      ];
      if ($phone_number_data['valid'] == FALSE) {
        $document = new Document();
        $document->setErrors(
              [
                'status' => '400',
                "title" => 'Invalid Phone Number',
                "detail" => 'the provided number is not recognized as a valid phone number',
              ]
          );
        return new ResourceResponse($document->toArray(), 400);
      }
      elseif ($phone_number_data['countryCode'] != 'US') {
        $document = new Document();
        $document->setErrors(
              [
                "status" => '400',
                "title" => 'Invalid Phone Number',
                "detail" => 'the provided number must must reside in the US',
              ]
                );

        return new ResourceResponse($document->toArray(), 400);
      }
      // Calls to the verify SMS function of the twilio service.
      // this is a paid request that returns mobile carrier information.
      $result = $twilio_connector_service->verifySms($phoneNumber);
      if ($result === 'mobile') {
        return new ResourceResponse(202);
      }
      else {
        $document = new Document();
        $document->setErrors(
              [
                "status" => '400',
                "title" => 'Invalid Phone Number',
                "detail" => 'the provided number must be a valid mobile number to recieve sms',
              ]
                );
        return new ResourceResponse($document->toArray(), 500);
      }
    }
    return new ResourceResponse($verifyNumber);
  }

}
