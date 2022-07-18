<?php

  namespace Drupal\ll_school\Form;

  use Drupal\Core\Form\FormBase;
  use Drupal\Core\Form\FormStateInterface;
  use Drupal\Core\Entity\EntityTypeManagerInterface;
  use Drupal\Core\Routing\RouteMatchInterface;
  use Symfony\Component\DependencyInjection\ContainerInterface;
  use Drupal\path_alias\AliasManagerInterface;
  use Drupal\Core\Path\CurrentPathStack;
  use Drupal\node\Entity\Node;
  use Drupal\Core\Url;
  use Drupal\Core\Routing\TrustedRedirectResponse;

  /**
   * Class implement filter form for block.
   */
  class ll_schoolForm extends FormBase{

    /**
     * The entity type manager.
     *
     * @var \Drupal\Core\Entity\EntityTypeManagerInterface
     */
    protected $entityTypeManager;

    /**
     * Drupal\Core\Entity\Query\QueryFactory definition.
     *
     * @var Drupal\Core\Entity\Query\QueryFactory
     */
    protected $entityQuery;
    /**
     * The current route match.
     *
     * @var \Drupal\Core\Routing\RouteMatchInterface
     */
    protected $routeMatch;

    /**
     * Current path.
     *
     * @var \Drupal\Core\Path\CurrentPathStack
     */
    protected $currentPath;

    /**
     * {@inheritdoc}
     */
    public function __construct(EntityTypeManagerInterface $entity_type_manager, RouteMatchInterface $route_match, AliasManagerInterface $alias_manager, CurrentPathStack $current_path) {

      $this->entityTypeManager = $entity_type_manager;
      $this->routeMatch = $route_match;
      $this->aliasManager = $alias_manager;
      $this->currentPath = $current_path;

    }

    /**
     * {@inheritdoc}
     */
    public static function create(ContainerInterface $container) {
      return new static(
        $container->get('entity_type.manager'),
        $container->get('current_route_match'),
        $container->get('path_alias.manager'),
        $container->get('path.current'),
      );
    }
    /**
      * {@inheritdoc}
    */
    public function getFormId(){
      return 'll_school';
    }
    /**
      * {@inheritdoc}
    */
    public function buildForm(array $form,FormstateInterface $form_state){

      // Getting nodes of school content type
			$query = \Drupal::entityQuery('node');
			$query->condition('status', 1);
			$query->condition('type', 'school', 'IN');
			$query->sort('title', 'ASC');
			$node_ids = $query->execute();

      foreach ($node_ids as $node_type) {
        $node = \Drupal::entityTypeManager()->getStorage('node')->load($node_type);
        $title[$node->id()] = $node->getTitle();
      }

      $ip_address = \Drupal::request()->getClientIp();
      //$ip_address = file_get_contents('https://api.ipify.org');
      $query = \Drupal::entityQuery('node');
			$query->condition('status', 1);
			$query->condition('type', 'school');
			$query->condition('field_school_ip', $ip_address, 'IN');
			$node_ids = $query->execute();
      if (!empty($node_ids)) {
        $nodes = Node::loadMultiple($node_ids);
        foreach ($nodes as $node_obj) {
          $school_ip = $node_obj->id();
        }
      }

      $form['search_option'] = array (
        '#type' => 'select',
        '#title' => $this->t('Search Option'),
        '#title_display' => 'invisible',
        '#empty_option' => $this->t('Select your Library'),
        '#options' => $title,
        '#default_value' => $school_ip,
      );
      $form['search_field'] = array(
        '#type' =>'textfield',
        '#title'=>t('Search field'),
        '#title_display' => 'invisible',
        '#default'=>'',
        '#attributes' => [
          'placeholder' => t('Type a Subject, Title, or Author'),
        ],
      );
      $form['submit']= array(
        '#type'=>'submit',
        '#value'=>'Search',
      );
      return $form;
    }
    public function submitForm(array &$form, FormStateInterface $form_state){ 
      $node_id = $form_state->getValue('search_option');
      $search_text = $form_state->getValue('search_field');
      if (!empty($node_id)) {
        $node_obj = \Drupal::entityTypeManager()->getStorage('node')->load($node_id);
        $school_id = $node_obj->get('field_school_id')->value;
        $myurl = "https://".$school_id.".library.nashville.org/Union/Search?lookfor=".$search_text."";
      } else {
        $myurl = "https://school.library.nashville.org/Union/Search?lookfor=".$search_text."";
      }
      //\Drupal::logger('search_text')->notice('<pre><code>' . print_r($search_text, TRUE) . '</code></pre>' );
      $response = new TrustedRedirectResponse(Url::fromUri($myurl)->toString());
      $form_state->setResponse($response);
    }
  }