Create a Silex application from this file:


```
Feature: application API

     Scenario: List users
       Given url "/api/users/list.json"
       And request method is "GET"
       Then instance "\Api\Users"
       And execute function "listUsers"
       And format output into json

     Scenario: Get user info
       Given url "/api/user/{userName}.json"
       And request method is "GET"
       Then instance "\Api\User"
       And execute function "info"
       And format output into json

     Scenario: Update user information
       Given url "/api/user/{userName}.json"
       And request method is "POST"
       Then instance "\Api\User"
       And execute function "update"
       And format output into json

```