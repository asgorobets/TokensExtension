Tokens Extension
=================

Installation
------------
Refer to [README.md](README.md#installation) for installation instructions

Usage
-----

In order to use step tokens effectively, you need to implement "Tokens Replacement" callees in your Context and provide an implementation of your own tokens replacement logic.
It's just a matter of defining an annotated method, very similar to how [Step argument transformations](http://docs.behat.org/en/v3.0/guides/2.definitions.html#step-argument-transformations) work.
Let's take a loot at an example:
`FeatureContext.php`
```php
class FeatureContext implements Context
{
    /**
     * Saved replacement tokens.
     *
     * @var array
     */
    private $saved_tokens = [];
    
    /**
     * @TokensReplacement /\[\[(.+?)\]\]/
     */
    public function replaceSavedStepTokens($value)
    {
        return isset($this->tokens[$value]) ? $this->tokens[$value] : false;
    }
}
```
Tokens are replaced based on discovered methods using annotations by defualt.
In order for tokens to be replaced, you need to have at least one method annotated with @TokensReplacement [regex goes here] annotation.
In the above case we used this annotation:
```php
 /**
  * @TokensReplacement /\[\[(.+?)\]\]/
  */
```
What it does?
When a step/argument text matches this pattern, it will execute out method and pass the captured group from the pattern as first parameter to our function.
In the above case it will match anything that starts with `[[` and ends with `]]` and pass anything in between to our method's $value param.
The method should do it's magic to provide a replacement and should either return a new value, if a replacement was found, or return FALSE
if there is no way to replace this token. In case of any non-false value being returned, Tokens extension will replace the token matching this pattern with returned value
everywhere it founds it.

Let's take an example of feature that will benefit from this extension:

```Gherkin
Feature: Document API
  Scenario: Document can be updated via API if user is sending existing doc id and list of fields to be updated
    Given I create a document using the UI with fields:
      | title  | file_contents  |
      | Doc 1  | abracadabra    |
    When I update document using API with fields:
      | doc_id                  | title                 |
      | [[last_created_doc_id]] | Doc 1 revised         |
    And I visit document page document/[[last_created_doc_id]]
    Then I should see "Document Title" field equal to "Doc 1 revised"
```
When testing something with Behat we want to be very context independent and don't rely on existing content, but sometimes it's
close to impossible to use predefined steps from different extensions as they ask you to hardcode values, values you may not yet know.
Using tokens we can define one or two custom steps that will be wrapping some other extension steps and save some information into tokens.
Later on we can leverage token replacement annotated methods to replace tokens with real values before step definitions are searched.
This will remove the ugly non-descriptive tokens and replace them with values not just when we receive them in the step definition method,
but also when we run the suite, so it's very transparent actually what we're testing, no magic for the real user running the suite is involved.

You can also define as many tokens replacement annotated methods as you like, and that way you can achieve greater flexibility.
For example you need to test 20 different documents being pushed via a service at the same time, maybe you have a list of document fields as YAML,
but you don't want to think of 20 unique IDs for your documents, maybe you just want to generate those IDs based on some pattern.
You could do something like this:
```php
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
```

This way you can generate random strings of and numbers just by using [[random:string]] and [[random:numeric]] tokens.
You can even save that generated number to your persistent tokens if you want with a step like:
```Gherkin
Scenario: Ability to use tokens in PyString node arguments
  Given tokens as YAML:
    """
    "numeric_token": "[[random:numeric]]"
    "string_token": "[[random:string]]"
    """
  Then "[[saved:numeric_token]]" should be a number between 0 and 10
  And "[[saved:string_token]]" should be a string with 10 characters
```
And the step itself:
```php
/**
 * @Given /^tokens as YAML:$/
 */
public function givenTokensYaml(PyStringNode $string) {
    $tokens = Yaml::parse($string);
    foreach ($tokens as $token_key => $value) {
        $this->saved_tokens[$token_key] = $value;
    }
}
```

FAQ
---

- Where can I use tokens?
Tokens will be replaced currently in step text, step arguments of type "Table" and "PyString". I plan to implement tokens replacement for Examples table for Scenario Outline, but this is not yet done. Pull Requests welcome ;)

- Why is my token not replaced?
Did you define an annotated method? Does it match the pattern? Did you returned non-false value by your annotated method? False means skip, anything else should replace the token entirely.

- Can I get some more context about what feature and step we're replacing tokens in, not just the text?
Absolutely, you can use StepNode $step as your second argument in your annotated method to get the parsed step.
```php
public function replaceStepTokens($type, StepNode $step)
{
}
```
- Why do I need to use annotations? It doesn't support my usecase, I want to do something more custom and more complex
Welp, you can do that, the annotations tokens replacer is just the defualt one, but the actual tokens replacers are pluggable.
You can register your own service in the service container and tag it with step tokens replacer tag, this is exactly how the extension
works right now, but there is room for anyone to customize, for example in your extension's `load` method you can do:
```php
/**
 * {@inheritdoc}
 */
public function load(ContainerBuilder $container, array $config)
{
  $definition = new Definition('Acme\MyExtension\AcmeStepTokensReplacer');
  $definition->addTag(TokensExtension::STEP_TOKENS_REPLACER_TAG, array('priority' => 50));
  $container->setDefinition(TokensExtension::STEP_TOKENS_REPLACER_TAG . '.acme', $definition);
}
```

- Isn't that too crazy? I can use Transformations to replace one value with another, no need to use some poor-written Extension.
Please, go ahead and use transfromations, but keep in mind:
  1. They will not affect your step definitions matching (no way to match a step definition if your token doesn't match your step regex)
  2. They will not support PyString (at the moment of writing)
  3. There is no way to use one Trasnformation pattern to rule all of the argument types and values, you have to declare Table and pattern trasnforms separately
  4. They were not designed to be used for tokens replacement use case, don't try to do something that I already tried =)
  
  
