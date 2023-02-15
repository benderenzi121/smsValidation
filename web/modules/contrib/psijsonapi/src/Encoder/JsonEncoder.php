<?php

namespace Drupal\psijsonapi\Encoder;

use Drupal\serialization\Encoder\JsonEncoder as SerializationJsonEncoder;

/**
 * Encodes JSON:API data.
 */
class JsonEncoder extends SerializationJsonEncoder {

  /**
   * The formats that this Encoder supports.
   *
   * @var string
   */
  protected static $format = ['psi_api_json'];

}
