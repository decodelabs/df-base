<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
declare(strict_types=1);
namespace Df\Flex\Xml;

use Df;
use Df\Flex\Formatter;

use DecodeLabs\Collections\AttributeContainer;
use DecodeLabs\Glitch;

class Node implements INode
{
    protected $element;

    /**
     * Create instance from file
     */
    public static function fromFile(string $path): INode
    {
        try {
            $document = static::newDomDocument();
            $document->load($path);
        } catch (\Throwable $e) {
            throw Glitch::EIo('Unable to load XML file', [
                'previous' => $e
            ]);
        }

        return static::fromDomDocument($document);
    }

    /**
     * Create instance from string
     */
    public static function fromString(string $xml): INode
    {
        $xml = trim($xml);

        if (!stristr($xml, '<?xml')) {
            $xml = '<?xml version="1.0" encoding="UTF-8"?>'."\n".$xml;
        }

        $xml = static::normalizeString($xml);

        try {
            $document = static::newDOMDocument();
            $document->loadXML($xml);
        } catch (\Throwable $e) {
            throw Glitch::EIo('Unable to load XML string', [
                'previous' => $e
            ]);
        }

        return static::fromDOMDocument($document);
    }

    /**
     * Create HTML instance from file
     */
    public static function fromHtmlFile(string $path): INode
    {
        try {
            $document = static::newDomDocument();
            $document->loadHtmlFile($path);
        } catch (\Throwable $e) {
            throw Glitch::EIo('Unable to load HTML file', [
                'previous' => $e
            ]);
        }

        return static::fromDomDocument($document);
    }

    /**
     * Create instance from string
     */
    public static function fromHtmlString(string $xml): INode
    {
        try {
            $document = static::newDOMDocument();
            $document->loadHTML($xml);
        } catch (\Throwable $e) {
            throw Glitch::EIo('Unable to load HTML string', [
                'previous' => $e
            ]);
        }

        return static::fromDOMDocument($document);
    }

    /**
     * Create instance from DOMDocument
     */
    public static function fromDomDocument(\DOMDocument $document): INode
    {
        $document->formatOutput = true;
        return new static($document->documentElement);
    }

    /**
     * Create a new DOMDocument
     */
    protected static function newDomDocument(): \DOMDocument
    {
        $output = new \DOMDocument();
        $output->formatOutput = true;
        return $output;
    }

    /**
     * Init with DOMElement
     */
    public function __construct(\DOMElement $element)
    {
        $this->element = $element;
    }

    /**
     * Replace this node element with a new tag
     */
    public function setTagName(string $name): INode
    {
        $newNode = $this->element->ownerDocument->createElement($name);
        $children = [];

        foreach ($this->element->childNodes as $child) {
            $children[] = $this->element->ownerDocument->importNode($child, true);
        }

        foreach ($children as $child) {
            $newNode->appendChild($child);
        }

        foreach ($this->element->attributes as $attrNode) {
            $child = $this->element->ownerDocument->importNode($attrNode, true);
            $newNode->setAttributeNode($attrNode);
        }

        $this->element->parentNode->replaceChild($newNode, $this->element);
        $this->element = $newNode;

        return $this;
    }

    /**
     * Get tag name of node
     */
    public function getTagName(): string
    {
        return $this->element->nodeName;
    }


    /**
     * Merge attributes on node
     */
    public function setAttributes(array $attributes): AttributeContainer
    {
        foreach ($attributes as $key => $value) {
            $this->setAttribute($key, $value);
        }

        return $this;
    }

    /**
     * Replace attribute on node
     */
    public function replaceAttributes(array $attributes): AttributeContainer
    {
        return $this->clearAttributes()->setAttributes($attributes);
    }

    /**
     * Set attribute on node
     */
    public function setAttribute($key, $value): AttributeContainer
    {
        $this->element->setAttribute($key, $value);
        return $this;
    }

    /**
     * Get all attribute values
     */
    public function getAttributes(): array
    {
        $output = [];

        foreach ($this->element->attributes as $attrNode) {
            $output[$attrNode->name] = $attrNode->value;
        }

        return $output;
    }

    /**
     * Get single attribute value
     */
    public function getAttribute(string $key)
    {
        return $this->element->getAttribute($key);
    }

    /**
     * Convert attribute to boolean
     */
    public function getBooleanAttribute(string $name): bool
    {
        return Formatter::toBoolean($this->getAttribute($name));
    }

    /**
     * Remove attribute list
     */
    public function removeAttribute(string ...$keys): AttributeContainer
    {
        foreach ($keys as $key) {
            $this->element->removeAttribute($key);
        }

        return $this;
    }

    /**
     * Does node have attribute?
     */
    public function hasAttribute(string ...$keys): bool
    {
        foreach ($keys as $key) {
            if ($this->element->hasAttribute($key)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Does node have attributes?
     */
    public function hasAttributes(string ...$keys): bool
    {
        foreach ($keys as $key) {
            if (!$this->element->hasAttribute($key)) {
                return false;
            }
        }

        return true;
    }

    /**
     * How many attributes?
     */
    public function countAttributes(): int
    {
        return $this->element->attributes->length;
    }

    /**
     * Remove all attributes
     */
    public function clearAttributes(): AttributeContainer
    {
        foreach ($this->element->attributes as $attrNode) {
            $this->element->removeAttribute($attrNode->name);
        }

        return $this;
    }




    /**
     * Set inner XML string
     */
    public function setInnerXml(string $inner): INode
    {
        $this->removeAllChildren();

        $fragment = $this->element->ownerDocument->createDocumentFragment();
        $fragment->appendXml($inner);
        $this->element->appendChild($fragment);

        return $this;
    }

    /**
     * Get string of all child nodes
     */
    public function getInnerXml(): string
    {
        $output = '';

        foreach ($this->element->childNodes as $child) {
            $output .= $this->element->ownerDocument->saveXML($child);
        }

        return $output;
    }

    /**
     * Normalize inner xml string
     */
    public function getComposedInnerXml(): string
    {
        $output = $this->getInnerXml();
        $output = preg_replace('/  +/', ' ', $output);
        $output = str_replace(["\r", "\n\n", "\n "], ["\n", "\n", "\n"], $output);
        return trim($output);
    }


    /**
     * Replace contents with text
     */
    public function setTextContent(string $content): INode
    {
        $this->removeAllChildren();

        $text = $this->element->ownerDocument->createTextNode($text);
        $this->element->appendChild($text);

        return $this;
    }

    /**
     * Get all text content in node
     */
    public function getTextContent(): string
    {
        return $this->element->textContent;
    }

    /**
     * Get ALL normalized text in node
     */
    public function getComposedTextContent(): string
    {
        $isRoot = $this->element === $this->element->ownerDocument->documentElement;
        $output = '';

        foreach ($this->element->childNodes as $node) {
            $value = null;

            switch ($node->nodeType) {
                case \XML_ELEMENT_NODE:
                    $value = (new static($node))->getComposedTextContent();

                    if ($isRoot) {
                        $value .= "\n";
                    }

                    break;

                case \XML_TEXT_NODE:
                    $value = ltrim($node->nodeValue);

                    if ($value != $node->nodeValue) {
                        $value = ' '.$value;
                    }

                    $t = rtrim($value);

                    if ($t != $value) {
                        $value = $t.' ';
                    }

                    break;

                case \XML_CDATA_SECTION_NODE:
                    if ($value) {
                        $value .= "\n";
                    }

                    $value .= trim($node->nodeValue)."\n";
                    break;
            }

            if (!empty($value)) {
                $output .= $value;
            }
        }

        return trim(str_replace(['  ', "\n "], [' ', "\n"], $output));
    }


    /**
     * Replace node content with CDATA
     */
    public function setCDataContent(string $content): INode
    {
        $this->removeAllChildren();

        $content = $this->element->ownerDocument->createCDataSection($content);
        $this->element->appendChild($content);

        return $this;
    }

    /**
     * Add CDATA section to end of node
     */
    public function prependCDataContent(string $content): INode
    {
        $content = $this->element->ownerDocument->createCDataSection($content);
        $this->element->insertBefore($content, $this->element->firstChild);

        return $this;
    }

    /**
     * Add CDATA section to start of node
     */
    public function appendCDataContent(string $content): INode
    {
        $content = $this->element->ownerDocument->createCDataSection($content);
        $this->element->appendChild($content);

        return $this;
    }

    /**
     * Get first CDATA section
     */
    public function getFirstCDataSection(): ?string
    {
        foreach ($this->element->childNodes as $node) {
            if ($node->nodeType == \XML_CDATA_SECTION_NODE) {
                return $node->nodeValue;
            }
        }

        return null;
    }

    /**
     * Get all CDATA sections within node
     */
    public function getAllCDataSections(): ?string
    {
        $output = [];

        foreach ($this->element->childNodes as $node) {
            if ($node->nodeType == \XML_CDATA_SECTION_NODE) {
                $output[] = $node->nodeValue;
            }
        }

        return $output;
    }


    /**
     * Count all child elements
     */
    public function count(): int
    {
        $output = 0;

        foreach ($this->element->childNodes as $node) {
            if ($node->nodeType == \XML_ELEMENT_NODE) {
                $output++;
            }
        }

        return $output;
    }

    /**
     * Count child elements of type
     */
    public function countType(string $name): int
    {
        $output = 0;

        foreach ($this->element->childNodes as $node) {
            if ($node->nodeType == \XML_ELEMENT_NODE
            && $node->nodeName == $name) {
                $output++;
            }
        }

        return $output;
    }

    /**
     * Does this node have any children?
     */
    public function hasChildren(): bool
    {
        if (!$this->element->childNodes->length) {
            return false;
        }

        foreach ($this->element->childNodes as $node) {
            if ($node->nodeType == \XML_ELEMENT_NODE) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get list of elements of type
     */
    public function __get(string $name): array
    {
        return $this->getChildList($name);
    }


    /**
     * Get all child elements
     */
    public function getChildren(): array
    {
        return $this->getChildList();
    }

    /**
     * Get first child element
     */
    public function getFirstChild(): ?INode
    {
        return $this->getFirstChildNode();
    }

    /**
     * Get last child element
     */
    public function getLastChild(): ?INode
    {
        return $this->getLastChildNode();
    }

    /**
     * Get child element by index
     */
    public function getNthChild(int $index): ?INode
    {
        return $this->getNthChildNode($index);
    }

    /**
     * Get list of children by formula
     */
    public function getNthChildren(string $formula): array
    {
        return $this->getNthChildList($formula);
    }


    /**
     * Get all children of type
     */
    public function getChildrenOfType(string $name): array
    {
        return $this->getChildList($name);
    }

    /**
     * Get first child of type
     */
    public function getFirstChildOfType(string $name): ?INode
    {
        return $this->getFirstChildNode($name);
    }

    /**
     * Get last child of type
     */
    public function getLastChildOfType(string $name): ?INode
    {
        return $this->getLastChildNode($name);
    }

    /**
     * Get child of type by index
     */
    public function getNthChildOfType(string $name, int $index): ?INode
    {
        return $this->getNthChildNode($index, $name);
    }

    /**
     * Get child of type by formula
     */
    public function getNthChildrenOfType(string $name, string $formula): array
    {
        return $this->getNthChildList($formula, $name);
    }


    /**
     * Shared child fetcher
     */
    protected function getChildList($name=null): array
    {
        $output = [];

        foreach ($this->element->childNodes as $node) {
            if ($node->nodeType == \XML_ELEMENT_NODE) {
                if ($name !== null && $node->nodeName != $name) {
                    continue;
                }

                $output[] = new static($node);
            }
        }

        return $output;
    }

    /**
     * Get first element in list
     */
    protected function getFirstChildNode(string $name=null): ?INode
    {
        foreach ($this->element->childNodes as $node) {
            if ($node->nodeType == \XML_ELEMENT_NODE) {
                if ($name !== null && $node->nodeName != $name) {
                    continue;
                }

                return new static($node);
            }
        }
    }

    /**
     * Get last element in list
     */
    protected function getLastChildNode(string $name=null): ?INode
    {
        $lastElement = null;

        foreach ($this->element->childNodes as $node) {
            if ($node->nodeType == \XML_ELEMENT_NODE) {
                if ($name !== null && $node->nodeName != $name) {
                    continue;
                }

                $lastElement = $node;
            }
        }

        return new static($lastElement);
    }

    /**
     * Get child at index
     */
    protected function getNthChildNode(int $index, string $name=null): ?INode
    {
        if ($index < 1) {
            throw Glitch::EInvalidArgument(
                $index.' is an invalid child index'
            );
        }

        foreach ($this->element->childNodes as $node) {
            if ($node->nodeType == \XML_ELEMENT_NODE) {
                if ($name !== null && $node->nodeName != $name) {
                    continue;
                }

                $index--;

                if ($index == 0) {
                    return new static($node);
                }
            }
        }
    }

    /**
     * Get children by formula
     */
    protected function getNthChildList(string $formula, string $name=null): array
    {
        if (is_numeric($formula)) {
            if ($output = $this->getNthChildNode($formula, $name)) {
                return [$output];
            }
        }

        $formula = strtolower($formula);

        if ($formula == 'even') {
            $formula = '2n';
        } elseif ($formula == 'odd') {
            $formula = '2n+1';
        }

        if (!preg_match('/^([\-]?)([0-9]*)[n]([+]([0-9]+))?$/i', str_replace(' ', '', $formula), $matches)) {
            throw Glitch::EInvalidArgument(
                $formula.' is not a valid nth-child formula'
            );
        }

        $mod = (int)$matches[2];
        $offset = (int)($matches[4] ?? 0);

        if ($matches[1] == '-') {
            $mod *= -1;
        }

        $output = [];
        $i = 0;

        foreach ($this->element->childNodes as $node) {
            if ($node->nodeType == \XML_ELEMENT_NODE) {
                if ($name !== null && $node->nodeName != $name) {
                    continue;
                }

                $i++;

                if ($i % $mod == $offset) {
                    $output[] = new static($node);
                }
            }
        }

        return $output;
    }





    /**
     * Get text content of first child of type
     */
    public function getChildTextContent(string $name): ?string
    {
        if (!$node = $this->getFirstChildOfType($name)) {
            return null;
        }

        return $node->getTextContent();
    }



    /**
     * Add child to end of node
     */
    public function prependChild($newChild, $value=null): INode
    {
        $node = $this->normalizeInputChild($newChild, $value);
        $node = $this->element->insertBefore($node, $this->element->firstChild);

        return new static($node);
    }

    /**
     * Add child to start of node
     */
    public function appendChild($newChild, $value=null): INode
    {
        $node = $this->normalizeInputChild($newChild, $value);
        $this->element->appendChild($node);

        return new static($node);
    }

    /**
     * Replace child node in place
     */
    public function replaceChild(INode $origChild, $newChild, $value=null): INode
    {
        $origChild = $origChild->getDomElement();
        $node = $this->normalizeInputChild($newChild, $value);
        $this->element->replaceChild($node, $origChild);

        return new static($node);
    }

    /**
     * Add child at index
     */
    public function putChild(int $index, $child, $value=null): INode
    {
        $newNode = $this->normalizeInputChild($child, $value);
        $origIndex = $index;
        $count = $this->count();
        $i = 0;

        if ($index < 0) {
            $index += $count;
        }

        if ($index < 0) {
            throw Glitch::EOutOfBounds(
                'Index '.$origIndex.' is out of bounds'
            );
        }

        if ($index === 0) {
            $newNode = $this->element->insertBefore($newNode, $this->element->firstChild);
        } elseif ($index >= $count) {
            $newNode = $this->element->appendChild($newNode);
        } else {
            foreach ($this->element->childNodes as $node) {
                if (!$node->nodeType == \XML_ELEMENT_NODE) {
                    continue;
                }

                if ($i >= $index + 1) {
                    $newNode = $this->element->insertBefore($newNode, $node);
                    break;
                }

                $i++;
            }
        }

        return new static($newNode);
    }

    /**
     * Add child node before chosen node
     */
    public function insertChildBefore(INode $origChild, $newChild, $value=null): INode
    {
        $origChild = $origChild->getDomElement();
        $node = $this->normalizeInputChild($newChild, $value);
        $this->element->insertBefore($node, $origChild);

        return new static($node);
    }

    /**
     * Add child node after chosen node
     */
    public function insertChildAfter(INode $origChild, $newChild, $value=null): INode
    {
        $origChild = $origChild->getDomElement();

        do {
            $origChild = $origChild->nextSibling;
        } while ($origChild && $origChild->nodeType != \XML_ELEMENT_NODE);

        $node = $this->normalizeInputChild($newChild, $value);

        if (!$origChild) {
            $this->element->appendChild($node);
        } else {
            $this->element->insertBefore($node, $origChild);
        }

        return new static($node);
    }

    /**
     * Remove child node
     */
    public function removeChild(INode $child): Node
    {
        $child = $child->getDomElement();
        $this->element->removeChild($child);
        return $this;
    }

    /**
     * Clear all children from node
     */
    public function removeAllChildren(): INode
    {
        $queue = [];

        foreach ($this->element->childNodes as $node) {
            $queue[] = $node;
        }

        foreach ($queue as $node) {
            $this->element->removeChild($node);
        }

        return $this;
    }


    /**
     * Get parent node
     */
    public function getParent(): ?INode
    {
        if (!$this->element->parentNode) {
            return null;
        }

        return new static($this->element->parentNode);
    }

    /**
     * How many other nodes are in parent
     */
    public function countSiblings(): int
    {
        if (!$this->element->parentNode) {
            return 0;
        }

        $output = -1;

        foreach ($this->element->parentNode->childNodes as $node) {
            if ($node->nodeType == \XML_ELEMENT_NODE) {
                $output++;
            }
        }


        if ($output < 0) {
            $output = 0;
        }

        return $output;
    }

    /**
     * Are there any other nodes in parent?
     */
    public function hasSiblings(): bool
    {
        if (!$this->element->parentNode) {
            return true;
        }

        if (!$this->element->previousSibling && !$this->element->nextSibling) {
            return true;
        }

        foreach ($this->element->parentNode->childNodes as $node) {
            if ($node === $this->element) {
                continue;
            }

            if ($node->nodeType == \XML_ELEMENT_NODE) {
                return false;
            }
        }

        return true;
    }


    /**
     * Get previous node
     */
    public function getPreviousSibling(): ?INode
    {
        $node = $this->element->previousSibling;

        while ($node && $node->nodeType != \XML_ELEMENT_NODE) {
            if (!$node = $node->previousSibling) {
                return null;
            }
        }

        if (!$node instanceof \DOMElement) {
            return null;
        }

        return new static($node);
    }

    /**
     * Get next node
     */
    public function getNextSibling(): ?INode
    {
        $node = $this->element->nextSibling;

        while ($node && $node->nodeType != \XML_ELEMENT_NODE) {
            if (!$node = $node->nextSibling) {
                return null;
            }
        }

        if (!$node instanceof \DOMElement) {
            return null;
        }

        return new static($node);
    }


    /**
     * Insert sibling before this node
     */
    public function insertBefore($sibling, $value=null): INode
    {
        $node = $this->normalizeInputChild($sibling, $value);
        $node = $this->element->parentNode->insertBefore($node, $this->element);

        return new static($node);
    }

    /**
     * Insert sibling after this node
     */
    public function insertAfter($sibling, $value=null): INode
    {
        $node = $this->normalizeInputChild($sibling, $value);

        $target = $this->element;

        do {
            $target = $target->nextSibling;
        } while ($target && $target->nodeType != \XML_ELEMENT_NODE);

        if (!$target) {
            $node = $this->element->parentNode->appendChild($node);
        } else {
            $node = $this->element->parentNode->insertBefore($node, $target);
        }

        return new static($node);
    }

    /**
     * Replace this node with another
     */
    public function replaceWith($sibling, $value=null): INode
    {
        $node = $this->normalizeInputChild($sibling, $value);
        $this->element->parentNode->replaceChild($node, $this->element);
        $this->element = $node;

        return $this;
    }




    /**
     * Get last comment before this node
     */
    public function getPrecedingComment(): ?string
    {
        if ($this->element->previousSibling
        && $this->element->previousSibling->nodeType == \XML_COMMENT_NODE) {
            return trim($this->element->previousSibling->data);
        }

        return null;
    }

    /**
     * Get all comments in node
     */
    public function getAllComments(): array
    {
        $output = [];

        foreach ($this->element->childNodes as $node) {
            if ($node->nodeType == \XML_COMMENT_NODE) {
                $output[] = trim($node->data);
            }
        }

        return $output;
    }



    /**
     * Get element by id
     */
    public function getById(string $id): ?INode
    {
        return $this->xPathFirst('//*[@id=\''.$id.'\']');
    }

    /**
     * Get all nodes of type
     */
    public function getByType(string $type): array
    {
        $output = [];

        foreach ($this->element->ownerDocument->getElementsByTagName($type) as $node) {
            $output[] = new static($node);
        }

        return $output;
    }

    /**
     * Get all nodes by attribute
     */
    public function getByAttribute(string $name, $value=null): array
    {
        if ($value == '') {
            $path = '//*[@'.$name.']';
        } else {
            $path = '//*[@'.$name.'=\''.$value.'\']';
        }

        return $this->xPath($path);
    }


    /**
     * Get nodes matching xPath
     */
    public function xPath(string $path): array
    {
        $xpath = new \DOMXPath($this->element->ownerDocument);
        $output = [];

        foreach ($xpath->query($path, $this->element) as $node) {
            $output[] = new static($node);
        }

        return $output;
    }

    /**
     * Get first xPath result
     */
    public function xPathFirst(string $path): ?INode
    {
        $xpath = new \DOMXPath($this->element->ownerDocument);
        $output = $xpath->query($path, $this->element)->item(0);

        if (!$output) {
            return null;
        }

        return new static($output);
    }


    /**
     * Set XML document version
     */
    public function setXmlVersion(string $version): INode
    {
        $this->element->ownerDocument->xmlVersion = $version;
        return $this;
    }

    /**
     * Get XML document version
     */
    public function getXmlVersion(): string
    {
        return $this->element->ownerDocument->xmlVersion;
    }

    /**
     * Set XML document encoding
     */
    public function setDocumentEncoding(string $encoding): INode
    {
        $this->element->ownerDocument->xmlEncoding = $encoding;
        return $this;
    }

    /**
     * Get XML document encoding
     */
    public function getDocumentEncoding(): string
    {
        return $this->element->ownerDocument->xmlEncoding;
    }

    /**
     * Set document as standalone
     */
    public function setDocumentStandalone(bool $flag): INode
    {
        $this->element->ownerDocument->xmlStandalone = $flag;
        return $this;
    }

    /**
     * Is document standalone?
     */
    public function isDocumentStandalone(): bool
    {
        return (bool)$this->element->ownerDocument->xmlStandalone;
    }

    /**
     * Normalize XML document
     */
    public function normalizeDocument(): INode
    {
        $this->element->ownerDocument->normalizeDocument();
        return $this;
    }



    /**
     * Get root document
     */
    public function getDomDocument(): \DOMDocument
    {
        return $this->element->ownerDocument;
    }

    /**
     * Get inner dom element
     */
    public function getDomElement(): \DOMElement
    {
        return $this->element;
    }

    /**
     * Ensure input is DomElement
     */
    protected function normalizeInputChild($child, $value=null): DOMElement
    {
        $node = null;

        if ($child instanceof INode) {
            $node = $child->getDOMElement();
        }

        if ($node instanceof \DOMElement) {
            $node = $this->element->ownerDocument->importNode($node, true);
        } else {
            $node = $this->element->ownerDocument->createElement((string)$child, $value);
        }

        return $node;
    }


    /**
     * Convert to string
     */
    public function __toString(): string
    {
        return $this->getComposedTextContent();
    }

    /**
     * Export document as string
     */
    public function toXmlString(): string
    {
        return $this->element->ownerDocument->saveXML();
    }

    /**
     * Export this nodes content as xml string
     */
    public function toNodeXmlString(): string
    {
        return $this->element->ownerDocument->saveXML($this->element);
    }

    /**
     * Normalize string for writing
     */
    protected static function normalizeString(string $string): string
    {
        return preg_replace('/[^\x{0009}\x{000a}\x{000d}\x{0020}-\x{D7FF}\x{E000}-\x{FFFD}]+/u', '', $string);
    }

    /**
     * Shortcut to set attribute
     */
    public function offsetSet($key, $value)
    {
        $this->setAttribute($key, $value);
    }

    /**
     * Shortcut to get attribute
     */
    public function offsetGet($key)
    {
        return $this->getAttribute($key);
    }

    /**
     * Shortcut to test for attribute
     */
    public function offsetExists($key)
    {
        return $this->hasAttribute($key);
    }

    /**
     * Shortcut to remove attribute
     */
    public function offsetUnset($key)
    {
        $this->removeAttribute($key);
    }


    /**
     * Dump inner xml
     */
    public function __debugInfo(): array
    {
        return [
            'xml' => $this->toNodeXmlString()
        ];
    }
}
