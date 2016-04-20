Feature: Step tokens replacement
  In order to reuse more predefined steps
  As a feature writer
  I need to be able to use replacement tokens to customize step text and arguments

  Background:
    Given a file named "features/bootstrap/FeatureContext.php" with:
      """
      <?php

      use Behat\Behat\Context\Context,
          Behat\Behat\Tester\Exception\PendingException;
      use Behat\Gherkin\Node\PyStringNode,
          Behat\Gherkin\Node\TableNode;
      use Symfony\Component\Yaml\Yaml;

      class FeatureContext extends PHPUnit_Framework_Assert implements Context
      {
          /**
           * Saved replacement tokens.
           *
           * @var array
           */
          private $saved_tokens = [];

          /**
           * @TokensReplacement /\[\[saved:(.+?)\]\]/
           */
          public function replaceSavedStepTokens($value)
          {
              return isset($this->saved_tokens[$value]) ? $this->saved_tokens[$value] : false;
          }

          /**
           * @TokensReplacement /\[\[random:(.+?)\]\]/
           */
          public function replaceRandomStepTokens($type)
          {
              switch ($type) {
                  case 'numeric':
                    return rand(0, 10);
                  case 'string':
                    return substr(uniqid(), 0, 10);
              }
          }

          /**
           * @Given /^I have "([^"]*)" token equal to "([^"]*)"$/
           */
          public function iHaveTokenEqualTo($token_key, $value) {
              $this->saved_tokens[$token_key] = $value;
          }

          /**
           * @Then /^"([^"]*)" is equal to "([^"]*)"$/
           */
          public function isEqualTo($actual, $expected) {
              PHPUnit_Framework_Assert::assertEquals($expected, $actual);
          }

          /**
           * @Given /^tokens as YAML:$/
           */
          public function givenTokensYaml(PyStringNode $string) {
              $tokens = Yaml::parse($string);
              foreach ($tokens as $token_key => $value) {
                  $this->saved_tokens[$token_key] = $value;
              }
          }

          /**
           * @Then /^"([^"]*)" should be a number between (\d+) and (\d+)$/
           */
          public function shouldBeNumberBetween($value, $min, $max) {
              $this->assertGreaterThanOrEqual($min, $value);
              $this->assertLessThanOrEqual($max, $value);
          }

          /**
           * @Given /^"([^"]*)" should be a string with (\d+) characters$/
           */
          public function shouldBeStringLength($value, $length) {
              $this->assertEquals(strlen($value), $length);
          }

          /**
           * @Given /^tokens table rows hash:$/
           */
          public function tokensTableHash(TableNode $table) {
              foreach ($table->getRowsHash() as $token_key => $value) {
                  $this->saved_tokens[$token_key] = $value;
              }
          }

          /**
           * @Given /^tokens table columns hash:$/
           */
          public function tokensTableColumns(TableNode $table) {
              foreach ($table->getColumnsHash() as $row_index => $tokens) {
                  foreach ($tokens as $token_key => $value) {
                      $this->saved_tokens[$token_key] = $value;
                  }
              }
          }

          /**
           * @Given /^I have a number (\d+)$/
           */
          public function iHaveANumber() {
              // Step definition not required, we just except the step to match.
          }

          /**
           * @Then /^uniqid (.+?) should match pattern "\/(.+?)\/"$/
           */
          public function iHaveAUniqidMatchingPattern($uniqid, $pattern) {
              $this->assertRegExp('/' . $pattern . '/', $uniqid);
          }
      }
      """
    And a file named "behat.yml" with:
      """
      default:
        extensions:
          Behat\TokensExtension: ~
      """

  Scenario: Ability to save tokens in one step and replace them in second step
    Given a file named "features/step_tokens_saved_tokens.feature" with:
      """
      Feature: Step tokens
        Scenario:
          Given I have "new_token" token equal to "My beautiful value"
          Then "[[saved:new_token]]" is equal to "My beautiful value"
      """
    When I run "behat -f progress --no-colors"
    Then it should pass with:
      """
      ..

      1 scenario (1 passed)
      2 steps (2 passed)
      """
  Scenario: Ability to use more than one token in step text
    Given a file named "features/step_tokens_multiple.feature" with:
      """
      Feature: Step tokens
        Scenario:
          Given I have "real_token" token equal to "My real value"
          Then "[[saved:real_token]]" is equal to "[[saved:real_token]]"
      """
    When I run "behat -f progress --no-colors"
    Then it should pass with:
      """
      ..

      1 scenario (1 passed)
      2 steps (2 passed)
      """
  Scenario: Ability to use tokens in PyString node arguments
    Given a file named "features/step_tokens_pystring.feature" with:
      """
      Feature: Step tokens
        Scenario:
          Given tokens as YAML:
            '''
            "numeric_token": "[[random:numeric]]"
            "string_token": "[[random:string]]"
            '''
          Then "[[saved:numeric_token]]" should be a number between 0 and 10
          And "[[saved:string_token]]" should be a string with 10 characters
      """
    When I run "behat -f progress --no-colors"
    Then it should pass with:
      """
      ...

      1 scenario (1 passed)
      3 steps (3 passed)
      """
  Scenario: Ability to use tokens in table node arguments as rows hash
    Given a file named "features/step_tokens_table_rows_hash.feature" with:
      """
      Feature: Step tokens
        Scenario:
          Given tokens table rows hash:
            | numeric_token | [[random:numeric]] |
            | string_token  | [[random:string]]  |
          Then "[[saved:numeric_token]]" should be a number between 0 and 10
          And "[[saved:string_token]]" should be a string with 10 characters
      """
    When I run "behat -f progress --no-colors"
    Then it should pass with:
      """
      ...

      1 scenario (1 passed)
      3 steps (3 passed)
      """
  Scenario: Ability to use tokens in table node arguments as columns hash
    Given a file named "features/step_tokens_table_columns_hash.feature" with:
      """
      Feature: Step tokens
        Scenario:
          Given tokens table columns hash:
            | numeric_token      | string_token      |
            | [[random:numeric]] | [[random:string]] |
          Then "[[saved:numeric_token]]" should be a number between 0 and 10
          And "[[saved:string_token]]" should be a string with 10 characters
      """
    When I run "behat -f progress --no-colors"
    Then it should pass with:
      """
      ...

      1 scenario (1 passed)
      3 steps (3 passed)
      """
  Scenario: Tokens replaced before step matching happens, so depending on token value a step can match or not
    Given a file named "features/step_tokens_replaced_before_matching.feature" with:
      """
      Feature: Step Tokens
        Scenario:
          Given I have a number [[random:numeric]]
          And I have "string_pattern" token equal to "/\w{10}/"
          Then uniqid [[random:string]] should match pattern "[[saved:string_pattern]]"
      """
    When I run "behat -f progress --no-colors"
    Then it should pass with:
      """
      ...

      1 scenario (1 passed)
      3 steps (3 passed)
      """
  Scenario: Tokens not found and not interpreted by Context are not replaced
    Given a file named "features/step_tokens_not_replaced.feature" with:
      """
      Feature: Step Tokens
        Scenario:
          Given I have a number [[not_a_number]]
      """
    When I run "behat -f progress --no-colors"
    Then it should pass with:
      """
      U

      1 scenario (1 undefined)
      1 step (1 undefined)
      """