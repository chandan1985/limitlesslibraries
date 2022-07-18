<?php

declare(strict_types=1);

namespace Drupal\link_url_decoder\Utility;

use Drupal\Core\GeneratedUrl;
use Drupal\Core\Utility\UnroutedUrlAssembler;

/**
 * Define the unrouted URL decoder assembler class.
 */
class UnroutedUrlDecoderAssembler extends UnroutedUrlAssembler {

  /**
   * @var bool
   */
  protected $decodeUrl;

  /**
   * Set the decode URL flag.
   *
   * @param bool $value
   *
   * @return \Drupal\link_url_decoder\Utility\UnroutedUrlDecoderAssembler
   */
  public function decodeUrl(bool $value = TRUE) {
    $this->decodeUrl = $value;

    return $this;
  }

  /**
   * {@inheritDoc}
   */
  protected function buildExternalUrl(
    $uri,
    array $options = [],
    $collect_bubbleable_metadata = FALSE
  ) {
    $url = parent::buildExternalUrl($uri, $options, $collect_bubbleable_metadata);

    if (!$this->decodeUrl) {
      return $url;
    }

    if ($url instanceof GeneratedUrl) {
      return $url->setGeneratedUrl(urldecode(
        $url->getGeneratedUrl()
      ));
    }

    return urldecode($url);
  }
}
