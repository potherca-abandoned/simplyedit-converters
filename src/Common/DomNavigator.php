<?php

namespace Potherca\SimplyEdit\Common;

use \DOMXpath;
use \DOMNode;
use \DOMNodeList;
use IvoPetkov\HTML5DOMDocument;

/**
 * Facade for DOMXpath and DOMDocument and convenience methods
 *
 * Uses IvoPetkov\HTML5DOMDocument in order to work with HTML5
 */
class DomNavigator
{
    ////////////////////////////// CLASS PROPERTIES \\\\\\\\\\\\\\\\\\\\\\\\\\\\
    /** @var HTML5DOMDocument */
    private $document;
    /** @var DOMXpath */
    private $xpath;

    use MagicFacadeTrait;

    //////////////////////////// SETTERS AND GETTERS \\\\\\\\\\\\\\\\\\\\\\\\\\\
    final protected function getSubjectForMagicFacade()
    {
        return [
            $this->document,
            $this->xpath,
        ];
    }

    //////////////////////////////// PUBLIC API \\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\
    final public function __construct(HTML5DOMDocument $document)
    {
        $this->document = $document;
        $this->xpath = new DOMXpath($this->document);
    }

    final public function getFirstElementByTagName($tagName)
    {
        $nodeList = $this->getElementsByTagName($tagName);

        $firstNode = $nodeList->item(0);

        return $firstNode;
    }

    /**
     * @param string $className
     * @param DOMNode $contextnode
     * @param bool $registerNodeNS
     *
     * @return DOMNodeList
     */
    final public function queryClass($className, DOMNode $contextnode = null, $registerNodeNS = true)
    {
        $expression = sprintf(
            './/*[contains(concat(" ", normalize-space(@class), " "), " %s ")]',
            $className
        );

        return $this->query($expression, $contextnode, $registerNodeNS);
    }

    /**
     * @param DOMNode $contextnode
     * @param bool $registerNodeNS
     *
     * @return DOMNodeList
     */
    final public function queryComments(DOMNode $contextnode = null, $registerNodeNS = true)
    {
        $expression = './/comment()';

        return $this->query($expression, $contextnode, $registerNodeNS);
    }

    /**
     * @param string $id
     * @param DOMNode $contextnode
     * @param bool $registerNodeNS
     *
     * @return DOMNodeList
     */
    final public function queryId($id, DOMNode $contextnode = null, $registerNodeNS = true)
    {
        $expression = sprintf('.//*[@id="%s"]', $id);

        return $this->query($expression, $contextnode, $registerNodeNS);
    }
}

/*EOF*/
