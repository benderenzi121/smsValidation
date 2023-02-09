<?php

namespace Drupal\test\Plugin\rest\resource;

use Drupal\rest\Plugin\ResourceBase;
use Drupal\rest\ResourceResponse;

/**
 * Provides a Demo Resource
 *
 * @RestResource(
 *   id = "test_resource",
 *   label = @Translation("TEST Resource"),
 *   uri_paths = {
 *     "canonical" = "/test"
 *   }
 * )
 */

class TestResource extends ResourceBase
{
    /**
     * Responds to entity GET requests.
     * 
     * @return \Drupal\rest\ResourceResponse
     */
    public function get() 
    {
        $response = ['message' => 'Hello, this is a rest service'];
        return new ResourceResponse($response);
    }
}
