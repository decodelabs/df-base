<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
namespace Df\Flex\Xml;

use DecodeLabs\Collections\AttributeContainer;

interface INode extends AttributeContainer, \Countable, \ArrayAccess
{
    // Node info
    public function setTagName(string $name): INode;
    public function getTagName(): string;

    // Attributes
    public function getBooleanAttribute(string $name): bool;

    // Content
    public function setInnerXml(string $inner): INode;
    public function getInnerXml(): string;
    public function getComposedInnerXml(): string;

    public function setTextContent(string $content): INode;
    public function getTextContent(): string;
    public function getComposedTextContent(): string;

    public function setCDataContent(string $content): INode;
    public function prependCDataContent(string $content): INode;
    public function appendCDataContent(string $content): INode;
    public function getFirstCDataSection(): ?string;
    public function getAllCDataSections(): ?string;

    // Child access
    public function countType(string $name): int;
    public function hasChildren(): bool;
    public function __get(string $name): array;

    public function getChildren(): array;
    public function getFirstChild(): ?INode;
    public function getLastChild(): ?INode;
    public function getNthChild(int $index): ?INode;
    public function getNthChildren(string $formula): array;

    public function getChildrenOfType(string $name): array;
    public function getFirstChildOfType(string $name): ?INode;
    public function getLastChildOfType(string $name): ?INode;
    public function getNthChildOfType(string $name, int $index): ?INode;
    public function getNthChildrenOfType(string $name, string $formula): array;

    public function getChildTextContent(string $name): ?string;

    // Child construction
    public function prependChild($child, $value=null): INode;
    public function appendChild($child, $value=null): INode;
    public function replaceChild(INode $origChild, $newChild, $value=null): INode;
    public function putChild(int $index, $child, $value=null): INode;
    public function insertChildBefore(INode $origChild, $newChild, $value=null): INode;
    public function insertChildAfter(INode $origChild, $newChild, $value=null): INode;
    public function removeChild(INode $child): Node;
    public function removeAllChildren(): INode;

    // Sibling access
    public function getParent(): ?INode;
    public function countSiblings(): int;
    public function hasSiblings(): bool;

    public function getPreviousSibling(): ?INode;
    public function getNextSibling(): ?INode;

    public function insertBefore($sibling, $value=null): INode;
    public function insertAfter($sibling, $value=null): INode;
    public function replaceWith($sibling, $value=null): INode;

    // Comments
    public function getPrecedingComment(): ?string;
    public function getAllComments(): array;

    // Global access
    public function getById(string $id): ?INode;
    public function getByType(string $type): array;
    public function getByAttribute(string $name, $value=null): array;

    public function xPath(string $path): array;
    public function xPathFirst(string $path): ?INode;

    // Document
    public function setXmlVersion(string $version): INode;
    public function getXmlVersion(): string;
    public function setDocumentEncoding(string $encoding): INode;
    public function getDocumentEncoding(): string;
    public function setDocumentStandalone(bool $flag): INode;
    public function isDocumentStandalone(): bool;
    public function normalizeDocument(): INode;

    // Conversion
    public function getDomDocument(): \DOMDocument;
    public function getDomElement(): \DOMElement;
    public function __toString(): string;
    public function toXmlString(): string;
    public function toNodeXmlString(): string;
}
