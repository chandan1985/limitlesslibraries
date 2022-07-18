<?php
  namespace Drupal\npl_redirects\Controller;
  use Drupal\Core\Controller\ControllerBase;
  use Drupal\Code\Database\Database;
  use Drupal\redirect\Entity\Redirect;
  use Drupal\Core\Url;
  use Drupal\Core\Link;

  class npl_redirectsController extends ControllerBase{
    public function createnpl_redirects(){
        $form = \Drupal::formBuilder()->getForm('Drupal\npl_redirects\Form\npl_redirectsForm');

        return [
             
            '#title'=>'NPL Redirects'
        ];
    }

    public function getnpl_redirectsList(){

           // The table header.
            $header = [
                $this->t('slno'),
                $this->t('Redirect URL'),
                $this->t('Target URL'),
                [
                'data' => $this->t('Actions'),
                'colspan' => 2,
                ],
            ];
            //select records from table
            $query = \Drupal::database()->select('npl_redirects', 'e');
            $query->fields('e', ['hash_key','redirect_url','target_url']);
            $results = $query->execute()->fetchAll();
            $rows=array();
            $count = 1;
                foreach ($results as $data) {
                    $url_delete = Url::fromRoute('npl_redirects.deletenpl_redirects', ['hash_key' => $data->hash_key], []);
                    $url_edit = Url::fromRoute('npl_redirects.createnpl_redirects', ['hash_key' => $data->hash_key], []);
                    $linkDelete = Link::fromTextAndUrl('Delete', $url_delete);
                    $linkEdit = Link::fromTextAndUrl('Edit', $url_edit);
              
                    //get data
                    $rows[] = array(
                        'serail_no' => $count.".",
                        'redirect_url' => $data->redirect_url,
                        'target_url' => $data->target_url,
                        'edit' =>  $linkEdit,
                        'delete' => $linkDelete,
                    );
                    $count++; 
                }
           //display data in site
           $form['table'] = [
            '#type' => 'table',
            '#header' => $header,
            '#rows' => $rows,
            '#empty' => t('No users found'),
        ];
        return [
            $form,
            '#title'=>'NPL Redirects',
            '#markup' => '<a href="/admin/structure/npl_redirects/add" type="button" class="btn btn-primary">Add redirects</a>'
        ];
    } 
    public function deletenpl_redirects($hash_key){
        $query = \Drupal::database();
        $query->delete('npl_redirects')
            ->condition('hash_key',$hash_key,'=')
            ->execute();

        $response = new \Symfony\Component\HttpFoundation\RedirectResponse('../admin/structure/npl_redirects');
        $response->send();
        $this->messenger()->addStatus('Npl_redirects deleted successfully.');
        return $response;
    }

  }