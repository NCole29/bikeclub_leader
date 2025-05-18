<?php

namespace Drupal\bikeclub_leader\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class ClubLeaderSettingsForm.
 *
 * @ingroup club
 */
class ClubLeaderSettingsForm extends FormBase {

  /**
   * Returns a unique string identifying the form.
   *
   * @return string
   *   The unique string identifying the form.
   */
  public function getFormId() {
    return 'club_leader_settings';
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Empty implementation of the abstract submit class.
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['club_leader_settings']['#markup'] =
    'New fields may be added on the <strong>Manage fields</strong> tab. To add new fields to public displays, edit the <e>Club Leaders</em> View.
    <p>Base fields (person name, position, start and end dates) cannot be deleted so they are not listed on the Manage fields page. Base fields may be rearranged on the <strong>Manage form display</strong> and <strong>Manage display</strong> tabs but this does not impact public displays of club leaders which are governed by Views.
    </p>
  
    View and edit the list of <a href="/admin/structure/taxonomy/manage/positions/overview">club positions</a>.';
  
    return $form;
  }

}
