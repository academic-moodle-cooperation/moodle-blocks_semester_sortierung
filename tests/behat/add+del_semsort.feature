@mod @block_semester_sortierung
 Feature: Add semester overview
   
   @javascript
   Scenario: Add semester overview
     Given the following "users" exist:
       | username | firstname | lastname | email |
       | teacher1 | Teacher | 1 | teacher1@asd.com |
     And the following "courses" exist:
       | fullname | shortname | category | startdate |
       | Course 1 | C1 | 0 | 1444425837 |
       | Course 2 | C2 | 0 | 1425936237 |
     And the following "course enrolments" exist:
       | user | course | role |
       | teacher1 | C1 | editingteacher |
       | teacher1 | C2 | editingteacher |
     When I log in as "teacher1"
     And I click on "Dashboard" "link" in the "Navigation" "block"
     And I click on "Customise this page" "button"
     And I add the "semester_sortierung" block 
     Then I should see "Course 1" in the "Semester overview" "block"
     And I click on "Actions" "link" in the "Semester overview" "block"
     And I click on "Delete Semester overview" "link"
     And I log out
