<?php

declare(strict_types=1);

namespace Drupal\link_url_decoder\Plugin\Field\FieldFormatter;

use Drupal\Component\Utility\Unicode;
use Drupal\Core\Annotation\Translation;
use Drupal\Core\Field\Annotation\FieldFormatter;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Path\PathValidatorInterface;
use Drupal\Core\Utility\UnroutedUrlAssemblerInterface;
use Drupal\link\Plugin\Field\FieldFormatter\LinkFormatter;
use Drupal\link_url_decoder\Utility\UnroutedUrlDecoderAssembler;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Define the link URL decoder formatter.
 *
 * @FieldFormatter(
 *   id = "link_url_decoder",
 *   label = @Translation("Link URL Decoder"),
 *   description = @Translation("Allow the URL to be rendered in the decoded format."),
 *   field_types = {"link"}
 * )
 */
class LinkUrlDecoderFormatter extends LinkFormatter {

  /**
   * @var \Drupal\Core\Utility\UnroutedUrlAssemblerInterface
   */
  protected $unroutedLinkUrlDecoderAssembler;

  /**
   * {@inheritDoc}
   */
  public function __construct(
    $plugin_id,
    $plugin_definition,
    FieldDefinitionInterface $field_definition,
    array $settings,
    $label,
    $view_mode,
    array $third_party_settings,
    PathValidatorInterface $path_validator,
    UnroutedUrlAssemblerInterface $unrouted_url_decoder_assembler
  ) {
    parent::__construct(
      $plugin_id,
      $plugin_definition,
      $field_definition,
      $settings,
      $label,
      $view_mode,
      $third_party_settings,
      $path_validator
    );
    $this->unroutedLinkUrlDecoderAssembler = $unrouted_url_decoder_assembler;
  }

  /**
   * {@inheritDoc}
   */
  public static function create(
    ContainerInterface $container,
    array $configuration,
    $plugin_id,
    $plugin_definition
  ) {
    return new self(
      $plugin_id,
      $plugin_definition,
      $configuration['field_definition'],
      $configuration['settings'],
      $configuration['label'],
      $configuration['view_mode'],
      $configuration['third_party_settings'],
      $container->get('path.validator'),
      $container->get('link_url_decoder.unrouted_url_assembler')
    );
  }

  /**
   * {@inheritDoc}
   */
  public static function defaultSettings(): array {
    return parent::defaultSettings() + [
      'external_url_decode' => FALSE
    ];
  }

  /**
   * {@inheritDoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state): array {
    $elements = parent::settingsForm($form, $form_state);

    $elements['url_only'] = [
      '#type' => 'checkbox',
      '#title' => t('URL only'),
      '#default_value' => $this->getSetting('url_only'),
      '#access' => $this->getPluginId() == 'link_url_decoder',
    ];
    $elements['url_plain'] = [
      '#type' => 'checkbox',
      '#title' => t('Show URL as plain text'),
      '#default_value' => $this->getSetting('url_plain'),
      '#access' => $this->getPluginId() == 'link_url_decoder',
      '#states' => [
        'visible' => [
          ':input[name*="url_only"]' => ['checked' => TRUE],
        ],
      ],
    ];
    $elements['external_url_decode'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Decode external URL'),
      '#default_value' => $this->getSetting('external_url_decode'),
    ];

    return $elements;
  }

  /**
   * Override \Drupal\link\Plugin\Field\FieldFormatter\LinkFormatter::viewElements
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $element = [];
    $entity = $items->getEntity();
    $settings = $this->getSettings();

    foreach ($items as $delta => $item) {
      // By default use the full URL as the link text.
      /** @var \Drupal\Core\Url $url */
      $url = $this->buildUrl($item);
      $link_title = $url->toString();

      // If the title field value is available, use it for the link text.
      if (empty($settings['url_only']) && !empty($item->title)) {
        // Unsanitized token replacement here because the entire link title
        // gets auto-escaped during link generation in
        // \Drupal\Core\Utility\LinkGenerator::generate().
        $link_title = \Drupal::token()->replace($item->title, [$entity->getEntityTypeId() => $entity], ['clear' => TRUE]);
      }

      // Trim the link text to the desired length.
      if (!empty($settings['trim_length'])) {
        $link_title = Unicode::truncate($link_title, $settings['trim_length'], FALSE, TRUE);
      }

      if (!empty($settings['url_only']) && !empty($settings['url_plain'])) {
        if ($url->isExternal() && !empty($settings['external_url_decode'])) {
          $link_title = urldecode($link_title);
        }
        $element[$delta] = [
          '#plain_text' => $link_title,
        ];

        if (!empty($item->_attributes)) {
          // Piggyback on the metadata attributes, which will be placed in the
          // field template wrapper, and set the URL value in a content
          // attribute.
          // @todo Does RDF need a URL rather than an internal URI here?
          // @see \Drupal\Tests\rdf\Kernel\Field\LinkFieldRdfaTest.
          $content = str_replace('internal:/', '', $item->uri);
          $item->_attributes += ['content' => $content];
        }
      }
      else {
        if ($url->isExternal() && !empty($settings['external_url_decode'])) {
          /** @var UnroutedUrlDecoderAssembler $url_assembler */
          $url_assembler = $this
            ->unroutedLinkUrlDecoderAssembler
            ->decodeUrl((bool) $settings['external_url_decode']);

          $url->setUnroutedUrlAssembler($url_assembler);
        }
        $element[$delta] = [
          '#type' => 'link',
          '#title' => $link_title,
          '#options' => $url->getOptions(),
        ];
        $element[$delta]['#url'] = $url;

        if (!empty($item->_attributes)) {
          $element[$delta]['#options'] += ['attributes' => []];
          $element[$delta]['#options']['attributes'] += $item->_attributes;
          // Unset field item attributes since they have been included in the
          // formatter output and should not be rendered in the field template.
          unset($item->_attributes);
        }
      }
    }

    return $element;
  }
}
