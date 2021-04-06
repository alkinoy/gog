Feature:
    In order to prove that the API works correctly
    WARNING this feature should be tested on newly created DB
    TO BE DONE: tests independent on Ids

    Scenario: Call a not found route
        When I add "Content-Type" header equal to "application/json"
        And I send a "GET" request to "/api/1/not-found-route"
        Then the response status code should be 404

    Scenario: Try get product list
        When I add "X-AUTH-TOKEN" header equal to "1be3856a-2708-4fdf-b9aa-3c76167c564a"
        And I add "Content-Type" header equal to "application/json"
        And I send a "GET" request to "/api/1/products"
        Then the response status code should be 200
        And the response should be in JSON
        And the JSON should be equal to:
            """
            {
              "productList": [
                {
                  "id": 1,
                  "title": "Fallout",
                  "price": 1.99,
                  "currency": "USD"
                },
                {
                  "id": 2,
                  "title": "Don't Strave",
                  "price": 2.99,
                  "currency": "USD"
                },
                {
                  "id": 3,
                  "title": "Baldur's Gate",
                  "price": 3.99,
                  "currency": "USD"
                }
              ]
            }
            """

    Scenario: Successfully create cart
        When I add "X-AUTH-TOKEN" header equal to "1be3856a-2708-4fdf-b9aa-3c76167c564a"
        And I add "Content-Type" header equal to "application/json"
        And I send a "POST" request to "/api/1/cart"
        Then the response status code should be 201
        And the response should be in JSON
        And the JSON should be equal to:
        """
            {
              "id": 1,
              "productCount": 0,
              "itemsCount": 0,
              "totalAmount": [],
              "productList": []
            }
        """