<?php
namespace Drupal\bikeclub_leader;

use Drupal\user\Entity\Role;
use Drupal\user\Entity\User;

/**
* Remove roles at the end of the leader's term.
* Cleanup process runs everytime a leader record is saved or edited.
*
*/
class ClubLeaderCleanup {

  /**
   * Roles assigned to positions.
   *
   * @var array
   */
  protected $roles;
  
  /**
   * Associative array of [position, role].
   *
   * @var array
   */
  protected $position_roles;

  /**
   * Roles to remove from user.
   *
   * @var array
   */
  protected $remove;

   /**
   * Array containing message contents.
   *
   * @var array
   */
  protected $message;

  /**
   * Returns today's date.
   *
   * @return date
   */
  public function today() {
    return strtotime(date('d-m-Y'));
  }

  /**
   * Get website roles assigned to positions.
   *
   * @return array
   *   An array containing ROLES.
   */
  public function assignedRoles() {
    // Associative array of [position, ROLE].
    $query = \Drupal::database()->query(
      "SELECT entity_id, field_website_role_target_id 
       FROM {taxonomy_term__field_website_role}");
    $this->position_roles = $query->fetchAllKeyed();
    ksort($this->position_roles);

    // Indexed array of unique assigned roles.
    $this->roles = array_unique(array_values($this->position_roles));

    return $this;
  }
  
  /**
   * Get past or current leaders with a Drupal account ('leader' = Drupal account).
   *
   * @return array
   *   An array containing all user ids.
   */
  public function getLeaders($gtorlt) {
    $query = \Drupal::database()->select('club_leader', 'c')
      ->condition('c.leader', 0, '>')
      ->condition('c.end_date', $this->today(), $gtorlt)
      ->fields('c', ['leader'])
      ->orderBy('c.leader');
    return $query->distinct()->execute()->fetchCol();
  }

  /**
   * Set role label.
   *
   * @return string
   */
  public function setRoleLabel($role) {
    $entity = Role::load($role);
    return $entity->label();
  }

  /**
   * For past leaders who are also current leaders,
   *  get list of "assigned roles" excluding current roles.
   */
  public function getRolesToRemove($id) {

    // List of current positions for user.
    $query = \Drupal::database()->select('club_leader','c')
      ->fields('c', ['position'])
      ->condition('leader', $id)
      ->condition('end_date', $this->today(), '>=');
    $current_positions = $query->execute()->fetchCol();

    $current_roles=[];
    foreach($current_positions as $position) {
      $current_roles[] = $this->position_roles[$position];
    }

    // Remove current roles from the unique list of roles assigned to positions.
    $this->remove = array_diff($this->roles, array_unique($current_roles));
    return $this->remove;
  }

  /**
   * Remove roles from user account.
   */  
  public function removeRoles($id, $roles) {
    $user = User::load($id);    
    $roles_removed = []; // Initialize for each user.

    foreach($roles as $role) {
      if ($user->hasRole($role)) {
        $roles_removed[] = $this->setRoleLabel($role);
        $user->removeRole($role);
        $user->save();
      }
    }
    if ($roles_removed) {
      $this->message[] = $user->label() . ' - ' . implode(", ", $roles_removed);
    }
  }

  /**
   * Identify past leaders.
   * If "past only", remove all roles assigned to leader positions. 
   * If "past and current", remove roles not associated with current position(s).
   */    
  public function roleCleanup() {
    $this->assignedRoles();

    $past_leaders = $this->getLeaders('<'); // end_date < today.

    if ($past_leaders) {
      $curr_leaders = $this->getLeaders('>='); // end_date >= today.
        
      $past_only = array_diff($past_leaders, $curr_leaders);  // Remove current leaders from past leader list.
      $past_curr = array_diff($past_leaders, $past_only);     // Remove past_only leaders from past leader list.

      // If user has only past leader records.
      if($past_only) {
        foreach ($past_only as $id) {
          $this->removeRoles($id, $this->roles); 
        }
      } 

      // If user has past and current leader records.
      if($past_curr) {
        foreach ($past_curr as $id) {
          $rolesToRemove = $this->getRolesToRemove($id);
          
          if ($rolesToRemove) {
            $this->removeRoles($id, $rolesToRemove); 
          }
        } 
      } 
      if($this->message) {
        \Drupal::messenger()->addMessage("Roles were removed for past leader positions:<br>" . implode("<br>",$this->message));
      }
    } 
  }
}
