<?php

namespace Drupal\bikeclub_leader\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class EligibleLeaderForm. Config form - set filter for Club Leader Eligible View.
 * 
 * @package Drupal\bikeclub_leader\Form
 */
class EligibleLeaderForm extends ConfigFormBase {
 /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'bikeclub_leader.adminsettings',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'eligible_role_form';
  }

  public function getRoles() {
    // Get list of all roles and remove from list: anonymous, administrator, and roles assigned to positions.

    $roles_obj = \Drupal::entityTypeManager()->getStorage('user_role')->loadMultiple();

    $allroles = [];
    foreach ($roles_obj as $role) {
      $allroles[] = [
        'id' => $role->id(),
        'label' => $role->label(),
      ];
    }
    $ids = array_column($allroles, 'id');
    $labels = array_column($allroles, 'label');
    $roles = array_combine($ids, $labels);
    asort($roles); 

    // Get array of roles associated with positions.
    $positions =  \Drupal::service('entity_type.manager')->getStorage("taxonomy_term")
      ->loadTree('positions',0,NULL,TRUE) ;
    
    $position_roles = [];
    foreach($positions as $position) {
      $prole = $position->get('field_website_role')->getValue();
      $prole = reset($prole);
      $position_roles[] = $prole['target_id']; 
    }    

    // Add to list of roles to exclude from select list.
    $position_roles[] = 'anonymous';
    $position_roles[] = 'administrator';

    foreach ($position_roles as $prole) {
      unset($roles["$prole"]); // Remove from list of roles.
    }

    // New label.
    foreach($roles as $key => $value) {
      if ($key == 'authenticated') {
        $roles[$key] = 'Any role'; 
      }
    }
    return $roles;
  }

   /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('bikeclub_leader.adminsettings');
    $roles = $this->getRoles();

    $form['eligible_role'] = [
      '#type' => 'select',
      '#title' => $this->t('Role required for club leaders'),
      '#description' => $this->t("Only persons with the required role can be selected when adding Club Leaders. 
        <br><em>Any role</em> means any role in addition to 'authenticated user'.
        <br>Common use case is to require club membership."),
      '#default_value' => $config->get('eligible_role'),
      '#options' => $roles,
    ];
 
    return parent::buildForm($form, $form_state);
  }

   /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);

    $elig_role = $form_state->getValue('eligible_role');

    $this->config('bikeclub_leader.adminsettings')
    ->set('eligible_role', $elig_role)
    ->save();

    $viewconfig = \Drupal::service('config.factory')->getEditable('views.view.club_leaders_eligible');

    if($elig_role == 'authenticated') {
      $viewconfig
       ->set('display.default.display_options.filters.roles_target_id.operator','not empty')
       ->set('display.default.display_options.filters.roles_target_id.value', NULL)
       ->save(); 
    } else {
      $viewconfig
       ->set('display.default.display_options.filters.roles_target_id.operator','or')
       ->set('display.default.display_options.filters.roles_target_id.value', [$elig_role])
       ->save(); 
    }
  }
}
