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

  Scenario: get list of the groups
    Given group "Captains" exists
    And group "Pirates" exists
    When API-user sends GET request to "/groups/"
    Then response should be standard JSON-success
    And the JSON node "success" should be true
    And the JSON node "data[0].name" should be equal to "Captains"
    And the JSON node "data[1].name" should be equal to "Pirates"

  @captureGroupId
  Scenario: update group info
    Given group "Admins" exists
    When API-user sends PUT request to update mentioned group
    """
    {"name":"Guests"}
    """
    Then the response status code should be 200
    And the JSON node "success" should be true
    Then mentioned group should be named as "Guests"

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
