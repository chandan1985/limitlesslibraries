<?php

declare(strict_types=1);

namespace Drupal\notification_message_ui_test\Controller;

use Drupal\Core\Controller\ControllerBase;

/**
 * Provides a page for a browser test to visit.
 */
final class TestPage extends ControllerBase {

  /**
   * Renders a test page.
   */
  public function build(): array {
    return [
      '#markup' => "Hello!",
    ];
  }

}
