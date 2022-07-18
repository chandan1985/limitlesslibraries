<?php
    namespace Drupal\npl_redirects\Form;

    use Drupal\Core\Form\FormBase;
    use Drupal\Core\Form\FormStateInterface;
    use Drupal\Code\Database\Database;
    use Drupal\Core\Messenger\MessengerTrait;

    class npl_redirectsForm extends FormBase{
        /**
         * {@inheritdoc}
         */

        public function getFormId(){
            return 'npl_redirects';
        }
        /**
         * {@inheritdoc}
         */
        public function buildForm(array $form,FormstateInterface $form_state){
            
            $form['redirect_url'] = array(
                '#type'=>'textfield',
                '#title'=> 'Redirect URL',
                '#required' => TRUE,
                '#default_value' => '',
            );
            $form['target_url'] = array(
                '#type'=>'textfield',
                '#title'=> 'Target URL',
                '#required' => TRUE,
                '#default_value' => '',
            );
            $form['submit']= array(
                '#type'=>'submit',
                '#value'=>'Add redirectd',
            );
            $form['cancel'] = array(
                '#type' => 'markup',
                '#markup' => '<a href="/admin/structure/npl_redirects">Cancel</a>',
            );
            return $form;
        }
        
        public function submitForm(array &$form, FormStateInterface $form_state){
             
            $database = \Drupal::database();
            $data = $database->select('npl_redirects','do')
                    ->fields('do',['hash_key']);
            $row_count = $data->execute()->fetchAll(); 
            $all_hash_key = [];
           
             foreach($row_count as $hashkey){
            //   // print_r($mail->email);
            //   // exit; 
              $all_hash_key[] = $hashkey->hash_key;
            }
            if(in_array(hash('sha256', $form_state->getValue('redirect_url')), $all_hash_key)){
              $this->messenger()->addWarning('hash_key already exists!');
            }else{
               $query = \Drupal::database();
               $query->insert('npl_redirects')
                    ->fields(array(
                      'hash_key' => hash('sha256', $form_state->getValue('redirect_url')),
                      'redirect_url' => $form_state->getValue('redirect_url'),
                      'target_url' => $form_state->getValue('target_url'),
                 
                    ))
                    ->execute();

                $response = new \Symfony\Component\HttpFoundation\RedirectResponse('../npl_redirects');
                $response->send();
                $this->messenger()->addStatus('URL saved successfully.');
            }
        }
    }