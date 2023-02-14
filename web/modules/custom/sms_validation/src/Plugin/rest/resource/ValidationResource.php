<?php

namespace Drupal\sms_validation\Plugin\rest\resource;

use Drupal\Core\Cache\CacheableMetadata;
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
    TwilioAPIConnector $twilio_service
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
      $container->get('twilio.api_connector')
    );
  }

  /**
   * Responds to entity GET requests.
   *
   * @return \Drupal\rest\ResourceResponse
   *   Returns a response based on whether the number is valid.
   */
  public function get($phoneNumber) {
    // @todo Add regex validation on Phone number. Return a 400 error for bad input.
    // Makes a request through twilio SDK.
    // Will return an empty array if the request could not be made.
    $verifyNumber = $this->twilioService->verifyNumber($phoneNumber);
    $errors = [];
    if (!empty($verifyNumber)) {
      if ($verifyNumber->valid === FALSE) {
        $errors[] = [
          'status' => '400',
          'title' => 'Invalid Phone Number',
          'detail' => 'The provided number is not recognized as a valid phone number',
        ];
      }
      elseif ($verifyNumber->countryCode !== 'US') {
        $errors[] = [
          'status' => '400',
          'title' => 'Invalid Phone Number',
          'detail' => 'The provided number must must reside in the US',
        ];
      }
      if (!empty($errors)) {
        $document = new Document();
        $document->setErrors($errors);
        $response = new ResourceResponse($document->toArray(), 400);
      }
      else {
        // Calls to the verify SMS function of the twilio service.
        // this is a paid request that returns mobile carrier information.
        $result = $this->twilioService->verifyNumber($phoneNumber, TRUE);
        if ($result === 'mobile') {
          $response = new ResourceResponse(NULL, 202);
        }
        else {
          $document = new Document();
          $document->setErrors(
            [
              'status' => '400',
              'title' => 'Invalid Phone Number',
              'detail' => 'The provided number must be a valid mobile number to recieve sms',
            ]
          );
          $response = new ResourceResponse($document->toArray(), 400);
        }
      }
    }
    // Cache all requests for 24 hours.
    $response->addCacheableDependency(CacheableMetadata::createFromRenderArray([
      '#cache' => [
        'max-age' => 60 * 60 * 24,
      ],
    ]));
    return $response;
  }

}
