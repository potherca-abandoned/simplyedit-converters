<?php

namespace Potherca\SimplyEdit\Converter\Html5Up;

use \DOMXpath;
use \DOMDocument;
use IvoPetkov\HTML5DOMDocument;
use Potherca\SimplyEdit\Common\DomNavigator;
use Potherca\SimplyEdit\Converter\ConverterInterface;

class StrataConverter implements ConverterInterface
{
    ////////////////////////////// CLASS PROPERTIES \\\\\\\\\\\\\\\\\\\\\\\\\\\\
    /** @var array */
    private $nameList = [];
    /** @var array */
    private $numbers = ['zero', 'one', 'two', 'three', 'four', 'five'];

    //////////////////////////// SETTERS AND GETTERS \\\\\\\\\\\\\\\\\\\\\\\\\\\
    final public function getNavigator()
    {
        return $this->navigator;
    }

    //////////////////////////////// PUBLIC API \\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\
    final public function __construct(DomNavigator $navigator)
    {
        $this->navigator = $navigator;
    }

    final public function convert()
    {
        $navigator = $this->navigator;

        $navigator->normalizeDocument();

        $this->removeComments($navigator);
        $this->convertTitleTag($navigator);
        $this->convertHeaderTags($navigator);
        /* @TODO:
        - Make the background image editable (and maybe also the color?)
        - Make images editable
        - Make "action" Buttons editable
        - Add templates (+ separate icons) for address
        - Add "<template>" tags where relevant (i.e. left-overs)
        */
        $this->convertSocialMediaIcons($navigator);
        $this->addSimplyEditLoaderSnippet($navigator);
        /* @TODO:
        - Add <meta property="profile:first_name|last_name|username|gender"/> tags
        - Add <meta property="business:contact_data:street_address|locality|postal_code|country_name /> tags
        - Insert a menu as the original template does not have one
        */

        return $navigator;
    }

    ////////////////////////////// UTILITY METHODS \\\\\\\\\\\\\\\\\\\\\\\\\\\\\
    private function convertTitleTag(DomNavigator $navigator)
    {
        $tagName = 'title';
        $element = $navigator->getFirstElementByTagName($tagName);
        $element->setAttribute('data-simply-field', 'meta '.$tagName);
    }

    private function convertHeaderTags(DomNavigator $navigator)
    {
        $this->convertH1Tags($navigator);
        $this->convertH2Tags($navigator);
    }

    private function convertH1Tags(DomNavigator $navigator)
    {
        // <h1><a href="index.html" data-simply-field="homelink">Spectral</a></h1>
        $element = $navigator->getFirstElementByTagName('h1');

        $name = $this->buildName($element);

        $element->setAttribute('data-simply-field', $name.' title');
    }

    private function convertH2Tags(DomNavigator $navigator)
    {
        // <h2 data-simply-field="banner title" data-simply-type="text">Spectral</h2>
        $nodeList = $navigator->getElementsByTagName('h2');

        foreach ($nodeList as $node) {

            $name = $this->buildName($node);

            $description = null;

            $sibling = $node->nextSibling;
            $parent = $node->parentNode;

            if ($sibling !== null && $sibling instanceof \DomElement && $sibling->tagName === 'p') {
                $description = $sibling;
            } elseif ($parent !== null && $parent->tagName === 'header') {
                $sibling = $parent->nextSibling;
                if ($sibling !== null && $sibling instanceof \DomElement && $sibling->tagName === 'p') {
                    $description = $sibling;
                }
            } else {
                // No description found
            }

            $node->setAttribute('data-simply-field', $name.' title');
            $node->setAttribute('data-simply-type', 'text');
            if ($description !== null) {
                $description->setAttribute('data-simply-field', $name.' description');
            }

            array_push($this->nameList, $name);
        }
    }

    private function convertSocialMediaIcons(DomNavigator $navigator)
    {
        $class = 'icons';
        $id = 'footer';
        $listName = sprintf('%s %s', $id, $class);

        $footer = $navigator->getElementById($id);
        $list = $this->getNavigator()->queryClass($class, $footer)->item(0);

        $list->setAttribute('data-simply-list', $listName);

        $listItems = $list->childNodes;

        for ($itemCounter = 0; $itemCounter < $listItems->length; $itemCounter++) {
            $listItem = $listItems->item($itemCounter);

            if ($listItem instanceof \DomElement) {
                $itemName = $listItem->textContent;

                $template = $navigator->createElement('template');
                $template->setAttribute('data-simply-template', $itemName);

                $list->replaceChild($template, $listItem);
                $template->appendChild($listItem);

                $link = $this->getNavigator()->query('.//a', $template)->item(0);
                $link->setAttribute('data-simply-field', 'link');

                $span = $this->getNavigator()->query('.//span', $template)->item(0);
                $span->setAttribute('data-simply-field', 'label');
            }
        }
    }

    private function addSimplyEditLoaderSnippet(DomNavigator $navigator)
    {
        $url = 'http://pother.ca/simplyedit-loader/simplyedit-loader.min.js';

        $snippet = file_get_contents($url);

        $snippet .= PHP_EOL . implode(PHP_EOL, [
            'simply.key("potherca");',
            'simply.set("endpoint", "https://github.com/potherca-bot/simplyedit-unicomplex");',
            // 'simply.src("https://canary.simplyedit.io/1/simply-edit.js", "website-potherca.c9users.io");',
        ]);

        $script = $navigator->createElement('script', $snippet);

        $body = $navigator->getFirstElementByTagName('body');

        $body->appendChild($script);
    }

    private function removeComments()
    {
        $comments = $this->getNavigator()->queryComments();

        foreach ($comments as $comment) {
            $comment->parentNode->removeChild($comment);
        }
    }

    private function buildName($node)
    {
        $name = 'unnamed';

        $abort = false;
        $parent = $node->parentNode;

        while ($abort === false) {
            if ($parent->hasAttribute('id')) {
                $name = $parent->getAttribute('id');
                $abort = true;
            } elseif ($parent->parentNode !== null) {
                $parent = $parent->parentNode;
            } else {
                $abort = true;
            }
        }

        if (array_key_exists($name, $this->numbers)) {
            $name = 'section '.$this->numbers[$name];
        }

        $found = in_array($name, $this->nameList);

        if ($found === true) {
            $name .= '-01';

            do {
                echo 'duplicate: '.$counter++;
                $name++;
                $found = in_array($name, $this->nameList);
            } while ($found === true);
        }

        return $name;
    }
}

/*EOF*/
