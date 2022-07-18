<?php

namespace Drupal\ll_school\Plugin\Block;
use Drupal\Core\Block\BlockBase;

/**
 * Provides a 'LLSchoolBlock' block.
 *
 * @Block(
 *  id = "ll_school_block",
 *  admin_label = @Translation("LL School block"),
 * )
 */
class LLSchoolBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    $build = [];
    $build['ll_school_block'] = \Drupal::formBuilder()->getForm('Drupal\ll_school\Form\ll_schoolForm');
    return $build;
  }
}
