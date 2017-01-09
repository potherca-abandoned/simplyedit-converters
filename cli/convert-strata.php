<?php

namespace Potherca\SimplyEdit\Converter;

use IvoPetkov\HTML5DOMDocument;
use Potherca\SimplyEdit\Converter\Html5Up\StrataConverter;
use Potherca\SimplyEdit\Common\DomNavigator;

require dirname(__DIR__).'/vendor/autoload.php';

function run ($parameters)
{
    if ($parameters === null || $parameters === false) {
        throw new Exception('Parameters seems to be empty', 64);
    } elseif (isset($parameters[1]) === false) {
        throw new Exception('No parameter given', 65);
    } elseif (is_readable($parameters[1]) === false || is_dir($parameters[1]) === true) {
        throw new Exception('Given parameter is not a readable file', 66);
    } else {
        $filePath = $parameters[1];
        $contents = file_get_contents($filePath);

        $document = new HTML5DOMDocument();

        $document->preserveWhitespace = false;
        $document->formatOutput = true;

        $document->loadHTML($contents);

        $navigator = new DomNavigator($document);

        $converter = new StrataConverter($navigator);

        $converter->convert();

        return $document->saveHTML();
    }
}


try {
    $exitCode = 0;
    $output = run ($argv);
    $stream = STDOUT;
} catch (Exception $exception) {
    $exitCode = $exception->getCode();
    $output = 'Error:' . $exception->getMessage();
    $stream = STDERR;
}

fwrite($stream, $output.PHP_EOL);

exit($exitCode);

/*EOF*/
