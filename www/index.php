<?php
include __DIR__ . '/../vendor/autoload.php';
define(FEATURE_PATH, __DIR__ . '/api.feature');

use Behat\Gherkin\Lexer,
        Behat\Gherkin\Parser,
        Behat\Gherkin\Keywords\ArrayKeywords,
        Behat\Gherkin\Node\FeatureNode,
        Behat\Gherkin\Node\ScenarioNode,
        Symfony\Component\HttpFoundation\Request,
        Silex\Application;

$keywords = new ArrayKeywords([
    'en' => [
        'feature' => 'Feature',
        'background' => 'Background',
        'scenario' => 'Scenario',
        'scenario_outline' => 'Scenario Outline',
        'examples' => 'Examples',
        'given' => 'Given',
        'when' => 'When',
        'then' => 'Then',
        'and' => 'And',
        'but' => 'But'
    ],
]);

function getMatch($subject, $pattern) {
    preg_match($pattern, $subject, $matches);
    return isset($matches[1]) ? $matches[1] : NULL;
}

$app = new Application();

function getScenarioConf($scenario) {
    $silexConfItem = [];

    /** @var $scenario  ScenarioNode */
    foreach ($scenario->getSteps() as $step) {
        $route = getMatch($step->getText(), '/^url "([^"]*)"$/');

        if (!is_null($route)) {
            $silexConfItem['route'] = $route;
        }

        $requestMethod = getMatch($step->getText(), '/^request method is "([^"]*)"$/');
        if (!is_null($requestMethod)) {
            $silexConfItem['requestMethod'] = strtoupper($requestMethod);
        }

        $instance = getMatch($step->getText(), '/^instance "([^"]*)"$/');
        if (!is_null($instance)) {
            $silexConfItem['className'] = $instance;
        }

        $method = getMatch($step->getText(), '/^execute function "([^"]*)"$/');
        if (!is_null($method)) {
            $silexConfItem['method'] = $method;
        }

        if ($step->getText() == 'format output into json') {
            $silexConfItem['jsonEncode'] = TRUE;
        }
    }
    return $silexConfItem;
}

/** @var $features FeatureNode */
$features = (new Parser(new Lexer($keywords)))->parse(file_get_contents(FEATURE_PATH), FEATURE_PATH);

foreach ($features->getScenarios() as $scenario) {
    $silexConfItem = getScenarioConf($scenario);
    $app->match($silexConfItem['route'], function (Request $request) use ($app, $silexConfItem) {
            function getConstructorParams($rClass, $request) {
                $parameters =[];
                foreach ($rClass->getMethod('__construct')->getParameters() as $parameter) {
                    if ('Symfony\Component\HttpFoundation\Request' == $parameter->getClass()->name) {
                        $parameters[$parameter->getName()] = $request;
                    }
                }
                return $parameters;
            }

            $rClass = new ReflectionClass($silexConfItem['className']);
            $obj = ($rClass->hasMethod('__construct')) ?
                    $rClass->newInstanceArgs(getConstructorParams($rClass, $request)) :
                    new $silexConfItem['className'];

            $output = $obj->{$silexConfItem['method']}();

            return ($silexConfItem['jsonEncode'] === TRUE) ? $app->json($output, 200) : $output;
        }
    )->method($silexConfItem['requestMethod']);
}

$app->run();