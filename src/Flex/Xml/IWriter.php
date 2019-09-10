<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
namespace Df\Flex\Xml;

use Df;
use DecodeLabs\Collections\AttributeContainer;

interface IWriter extends AttributeContainer, \ArrayAccess
{
    // Header
    public function writeHeader(string $version='1.0', string $encoding='UTF-8', bool $standalone=false): IWriter;
    public function writeDtd(string $name, string $publicId=null, string $systemId=null, string $subset=null): IWriter;
    public function writeDtdAttlist(string $name, string $content): IWriter;
    public function writeDtdElement(string $name, string $content): IWriter;
    public function writeDtdEntity(string $name, string $content, string $pe, string $publicId, string $systemId, string $nDataId): IWriter;

    // Element
    public function writeElement(string $name, string $content=null, array $attributes=null): IWriter;
    public function startElement(string $name, array $attributes=null): IWriter;
    public function endElement(): IWriter;
    public function setElementContent(string $content): IWriter;
    public function getElementContent(): ?string;

    // CData
    public function writeCData(string $content): IWriter;
    public function writeCDataElement(string $name, string $content, array $attributes=null): IWriter;
    public function startCData(): IWriter;
    public function writeCDataContent(string $content): IWriter;
    public function endCData(): IWriter;

    // Comment
    public function writeComment(string $comment): IWriter;
    public function startComment(): IWriter;
    public function writeCommentContent(string $comment): IWriter;
    public function endComment(): IWriter;

    // PI
    public function writePi(string $target, string $content): IWriter;
    public function startPi(string $target): IWriter;
    public function writePiContent(): IWriter;
    public function endPi(): IWriter;

    // Attributes
    public function setRawAttributeNames(string ...$names): IWriter;
    public function getRawAttributeNames(): array;

    // Raw
    public function writeRaw(string $content): IWriter;

    // IO
    public function finalize(): IWriter;
    public function toReader(): INode;
    public function importReader(INode $reader);
    public function __toString(): string;
}
