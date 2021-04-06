Feature:
    In order to prove that the project is alive
    I want to have a ping scenario

    Scenario: It receives a response from Symfony's kernel
        When a ping scenario sends a request to "/ping"
        Then the response "pong" should be received
