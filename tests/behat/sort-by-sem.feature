@mod @block_semester_sortierung
Feature: Change semesters
   
  @javascript
  Scenario: Add semester overview
    Given the following "users" exist:
        | username | firstname | lastname | email |
        | teacher1 | Teacher | 1 | teacher1@asd.com |
    And the following "courses" exist:
        | fullname | shortname | category | startdate |
        | Course 1 | C1 | 0 | 1460386247 |  
        | Course 2 | C2 | 0 | 1444575047 |
    And the following "course enrolments" exist:
        | user | course | role |
        | teacher1 | C1 | editingteacher |
        | teacher1 | C2 | editingteacher |
    
    When I log in as "admin" 
    And I navigate to "Semester overview" node in "Site administration > Plugins > Blocks"
    And I set the following fields to these values:
    	| Sort courses by semester | 1 |
    Then I press "Save changes"
    And I log out	
    When I log in as "teacher1"
    And I click on "Dashboard" "link" in the "Navigation" "block"
    And I click on "Customise this page" "button"
    And I add the "semester_sortierung" block 

    Then I should see "Winter term" in the "Semester overview" "block"
    Then I should see "Summer term" in the "Semester overview" "block"
    And I log out

    When I log in as "admin" 
    And I navigate to "Semester overview" node in "Site administration > Plugins > Blocks"
    And I set the following fields to these values:
    	| Sort courses by semester | 0 |
    Then I press "Save changes"
    And I log out
    When I log in as "teacher1"
    And I click on "Dashboard" "link" in the "Navigation" "block"
     
    Then I should not see "Winter term" in the "Semester overview" "block"
    Then I should not see "Summer term" in the "Semester overview" "block"
    And I log out