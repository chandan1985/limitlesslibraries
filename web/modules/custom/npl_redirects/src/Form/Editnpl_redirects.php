<?php
    namespace Drupal\npl_redirects\Form;
    use Drupal\Core\Form\FormBase;
    use Drupal\Core\Form\FormStateInterface;
    use Drupal\Code\Database\Database;
    use Drupal\Core\Messenger\MessengerTrait;
    use Drupal\Core\Url;
    use Drupal\Core\Link;

    class Editnpl_redirects extends FormBase{
        /**
         * {@inheritdoc}
         */

        public function getFormId(){
            return 'edit_npl_redirects';
        }
        /**
         * {@inheritdoc}
         */
        public function buildForm(array $form,FormstateInterface $form_state){
            $hash_key = \Drupal::routeMatch()->getparameter('hash_key');
            // $url_delete = Url::fromRoute('npl_redirects.deletenpl_redirects', ['id' => $data->id], []);
            // $linkDelete = Link::fromTextAndUrl('Delete', $url_delete);

            $query = \Drupal::database();
            $data = $query->select('npl_redirects','e')
                   ->fields('e',['hash_key','redirect_url','target_url'])
                   ->condition('e.hash_key',$hash_key,'=')
                   ->execute()->fetchAll(\PDO::FETCH_OBJ);
                    // print_r($data);
            $form['redirect_url'] = array(
                '#type'=>'textfield',
                '#title'=> '  redirect_url',
                '#required' => TRUE,
                '#default_value'=>$data[0]->redirect_url,
            );
            $form['target_url'] = array(
                '#type'=>'textfield',
                '#title'=> ' target_url',
                '#required' => TRUE,
                '#default_value'=>$data[0]->target_url,
            );
            $form['update']= array(
                '#type'=>'submit',
                '#value'=>'update',
            );
            $form['cancel'] = array(
                '#type' => 'markup',
                '#markup' => '<a href="/admin/structure/npl_redirects">Cancel</a>',
            );
            // $form['delete'] = array(
            //     '#type' => 'markup',
            //     '#markup' => '<a href="/admin/structure/npl_redirects/delete/' . $linkDelete . '">Delete</a> | ',
            //   );
            return $form;
        }
        
        /**
         * {@inheritdoc}
         */
        public function submitForm(array &$form, FormStateInterface $form_state){
            $hash_key = \Drupal::routeMatch()->getparameter('hash_key');
            $postData = $form_state->getValues();

            /**
             * Remove the unwanted keys form postDate
            */
           
            unset($postData['form_build_id'],$postData['form_token'],$postData['form_id'],$postData['op'],$postData['update']);           
            $query = \Drupal::database();
            $query->update('npl_redirects')->fields($postData)
                ->condition('hash_key',$hash_key)
                ->execute();

            $response = new \Symfony\Component\HttpFoundation\RedirectResponse('../npl_redirects');
            $response->send();
            $this->messenger()->addStatus('URL updated successfully.');
        }
    }