@mod @block_semester_sortierung
Feature: Change WS and SS
   
  @javascript
  Scenario: Change WS and SS
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
    When I log in as "teacher1"
    And I click on "Dashboard" "link" in the "Navigation" "block"
    And I click on "Customise this page" "button"
    And I add the "semester_sortierung" block

    Then I should see "Winter term" in the "Semester overview" "block"
    Then I should see "Summer term" in the "Semester overview" "block"

    Then "Winter term 2015/2016" "fieldset" should exist
    Then "Summer term 2016" "fieldset" should exist


    And I log out

    When I log in as "admin"
    And I navigate to "Semester overview" node in "Site administration > Plugins > Blocks"
    And I set the following fields to these values:
        | Januar    | 0 |
        | Februar   | 0 |
        | March     | 1 |
        | April     | 1 |
        | May       | 1 |
        | June      | 1 |
        | July      | 1 |
        | August    | 0 |
        | September | 0 |
        | October   | 0 |
        | November  | 0 |
        | December  | 0 |
    Then I press "Save changes"
    And I log out
    When I log in as "teacher1"

    Then I should see "Winter term" in the "Semester overview" "block"
	And I log out

	When I log in as "admin"
    And I navigate to "Semester overview" node in "Site administration > Plugins > Blocks"
    And I set the following fields to these values:
        | Januar    | 1 |
        | Februar   | 1 |
        | March     | 0 |
        | April     | 0 |
        | May       | 0 |
        | June      | 0 |
        | July      | 0 |
        | August    | 0 |
        | September | 1 |
        | October   | 1 |
        | November  | 1 |
        | December  | 1 |
    Then I press "Save changes"
    And I log out

	When I log in as "teacher1"

    Then I should see "Summer term" in the "Semester overview" "block"

   



     
    And I log out
