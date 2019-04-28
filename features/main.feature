Feature: groups and users management
  User Stories:
  - I want to create a user who is included in a group
  - I want to check if this user exits and is active
  - I want to modify the list of users of a group

  Scenario: Create a group
    When I create a group
    Then a group was created
    And I see a group

  Scenario: Throw error on create group with same name
    Given there is a group
    When I create group with same name
    Then request is invalid

  Scenario: Get list of the groups
    Given there is a groups
    When I get a list of groups
    Then I see a list of groups

  Scenario: Update group info
    Given there is a group
    When I update group info
    Then group info was updated

  @captureCreateUser
  Scenario: Create User
    Given group "Innovators" exists
    When API-user sends POST request to "/users/"
    """
    {"firstName":"Elon", "lastName": "Musk", "email": "ElonMuskOffice@TeslaMotors.com", "isActive":true, "groupId": {$1}}
    """
    Then user "Elon Musk" should be created
    And the response status code should be 201
    And response should contain created user id

  Scenario: Fetch list of users
    Given there is a users in a group
    When I get a list of all users
    Then I see a list of all users

  Scenario: Fetch info of a user
    Given there is a user
    When I get a user
    Then I see a user

  Scenario: Modify user
    Given there is a user
    When I update user info
    Then user info was updated
