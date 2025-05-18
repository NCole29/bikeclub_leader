<?php

namespace Drupal\bikeclub_leader\Hook;

use Drupal\Core\Url;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Hook\Attribute\Hook;

/**
 * Hook implementations for bikeclub_leader.
 */
class HelpHook {

  /**
   * Implements hook_help().
   */
  #[Hook('help')]
  public function help($route_name, RouteMatchInterface $route_match) {
    switch ($route_name) {
      case 'help.page.bikeclub_leader':
        $output = '';
        $output .= '<h2>' . t('About') . '</h2>';
        $output .= '<p>' . t('The Bikeclub leader module allows you to maintain a list of current and past club officers, directors, and coordinators.') . '</p>';

        $output .= '<h2>' . t('Uses') . '</h2>';
        $output .= '<dl>';
        $output .= '<dt>' . t('<strong>Manage</strong> the <a href=":positions">Club positions</a> taxonomy.<br>Fields: ', [
          ':positions' => Url::fromRoute('entity.taxonomy_vocabulary.overview_form', ['taxonomy_vocabulary' => 'positions',])->toString()]) . '</dt>';
        $output .= '<dd><ul><li>' . t('<em>Name</em> (text field) is the name of the position (e.g., President, Membership coordinator).') . '</li>';
        $output .= '<li>' . t('<em>Category</em> (select list) is used along with the sort order of positions to organize the list of leaders on public pages.*') . '</li>';
        $output .= '<li>' . t('<em>Drupal Role</em> (select list) specifies the role (with associated website permissions) assigned to persons in a position for the duration of their term.') . '</li>';
        $output .= '<li>' . t('<em>Disabled</em> (checkbox) may be selected to prevent assignment of positions that are no longer in use. DO NOT delete positions that were used in the past.') . '</li>';
        $output .= '</ul></dd></dt>';
        $output .= '<p>* To edit the list of categories, go to Structure > Taxonomy > Club positions > Manage fields.</p>';

        $output .= '<dt>' . t('<strong>View and add</strong> <a href=":leaders">Club Leaders</a>.<br>Fields:', [
        ':leaders' => Url::fromRoute('view.club_leaders.edit')->toString()]) . '</dt>';
        $output .= '<dd><ul>';
        $output .= '<li>' . t('<em>Leader</em> (autocomplete) is the name of a person holding a leadership position. Persons must have a Drupal user account.**') . '</li>';
        $output .= '<li>' . t('<em>Club position</em> (select list) is the club position.') . '</li>';
        $output .= '<li>' . t('<em>Start and end dates</em> define the term of service, place persons on the lists of <em>current</em> or <em>past</em> leaders, and are used to enable/disable the associated Drupal role for the term of service.') . '</li>';
        $output .= '</ul></dd></dt>';
        $output .= '<p>** The Leader autocomplete field may be restricted to Drupal users with certain Drupal roles - for example, <em>Members</em>. To apply this restriction, go to Configuration > People > Club leaders.</p>';


        $output .= '<dt>' . t('<strong>Public web pages</strong> with current and past <a href=":viewcl">Club Leaders</a>. ', [
          ':viewcl' => Url::fromRoute('view.club_leaders.current')->toString()]) . '</dt>';
        $output .= '<dd>' . t('The Club Leaders View provides public pages. Add <em>Club leaders</em> to the main menu if it does not already exist, with link "/club-leaders".') . '</dd>';
        $output .= '</dt></dl>';


        $output .= '<h2>' . t('Notes') . '</h2>';
        $output .= '<dl><dt>This module:';
        $output .= '<dd><ul>';
        $output .= '<li>Creates a custom entity (ClubLeader), with code to assign the Drupal Role to a club leader when the leader record is saved</li>';
        $output .= '<li>Includes a form hook to customize the <em>Club positions</em> taxonomy overview page</li>';
        $output .= '<li>Includes <em>cleanup</em> code to remove assigned Drupal roles from users when their leader term concludes. 
        This code runs when a leader record is saved and checks the term dates and assigned roles of all leaders, taking account of the possibility of re-election.</li>';
        $output .= '</ul></dd></dt></dl>';
        $output .= '<p>' . t('For leaders who serve multiple consecutive terms, be sure to add a new record to the database for each term to provide a complete list of current and past leaders.');
        $output .= ' Do not simply change the <em>End date</em> for leaders who are re-elected.</p>';
        return $output;
    }
  }

}
