<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
declare(strict_types=1);
namespace Df\Html;

use Df;

use Df\Data\IAttributeContainer;
use Df\Data\TAttributeContainer;

use DecodeLabs\Gadgets\Pipe;
use DecodeLabs\Gadgets\PipeTrait;

class Tag implements ITag, Pipe
{
    use PipeTrait;
    use TAttributeContainer;

    const CLOSED_TAGS = [
        'area', 'base', 'br', 'col', 'command', 'embed',
        'hr', 'img', 'input', 'keygen', 'link', 'meta',
        'param', 'source', 'wbr'
    ];

    const INLINE_TAGS = [
        'a', 'br', 'bdo', 'abbr', 'blink', 'nextid', 'acronym', 'basefont',
        'b', 'em', 'big', 'cite', 'input', 'spacer', 'listing',
        'i', 'rp', 'del', 'code', 'label', 'strike', 'marquee',
        'q', 'rt', 'ins', 'font', 'small', 'strong',
        's', 'tt', 'sub', 'mark',
        'u', 'xm', 'sup', 'nobr',
                   'var', 'ruby',
                   'wbr', 'span',
                          'time',
    ];

    const BOOLEAN_ATTRIBUTES = [
        'spellcheck'
    ];

    protected $name;
    protected $closable = true;
    protected $renderEmpty = true;

    /**
     * Can tag be closed with full </tag>
     */
    public static function isClosableTagName($name): bool
    {
        return in_array(strtolower($name), self::CLOSED_TAGS);
    }

    /**
     * Should tag be single inline entity
     */
    public static function isInlineTagName($name): bool
    {
        return in_array(strtolower($name), self::INLINE_TAGS);
    }


    /**
     * Init with name and attributes
     */
    public function __construct(string $name, array $attributes=null)
    {
        $this->setName($name);

        if ($attributes !== null) {
            $this->addAttributes($attributes);
        }
    }

    /**
     * Parse css style selector into tag name, classes, etc
     */
    public function setName(string $name): ITag
    {
        if (false !== strpos($name, '[')) {
            $name = preg_replace_callback('/\[([^\]]*)\]/', function ($res) {
                $parts = explode('=', $res[1], 2);
                $key = array_shift($parts);
                $value = array_shift($parts);
                $first = substr($value, 0, 1);
                $last = substr($value, -1);

                if (strlen($value) > 1
                && (($first == '"' && $last == '"')
                || ($first == "'" && $last == "'"))) {
                    $value = substr($value, 1, -1);
                }

                $this->setAttribute($key, $value);
                return '';
            }, $name);
        }

        if (false !== strpos($name, '#')) {
            $name = preg_replace_callback('/\#([^ .\[\]]+)/', function ($res) {
                $this->setId($res[1]);
                return '';
            }, $name);
        }

        $parts = explode('.', $name);
        $this->name = array_shift($parts);

        if (false !== ($pos = strpos($this->name, '?'))) {
            $this->name = str_replace('?', '', $this->name);
            $this->renderEmpty = false;
        }

        if (!empty($parts)) {
            $this->addClasses(...$parts);
        }

        return $this;
    }

    /**
     * Get tag name
     */
    public function getName(): string
    {
        return $this->name;
    }


    /**
     * Is this element inline?
     */
    public function isInline(): bool
    {
        return in_array(strtolower($this->name), self::INLINE_TAGS);
    }

    /**
     * Is this a block element?
     */
    public function isBlock(): bool
    {
        return !$this->isInline();
    }


    /**
     * Create opening tag string
     */
    public function open(): string
    {
        $attributes = [];

        foreach ($this->attributes as $key => $value) {
            if ($value === null) {
                $attributes[] = $key;
            } elseif (is_bool($value)) {
                if (substr($key, 0, 5) == 'data-' || in_array($key, static::BOOLEAN_ATTRIBUTES)) {
                    $attributes[] = $key.'="'.($value ? 'true' : 'false').'"';
                } else {
                    if ($value) {
                        $attributes[] = $key;
                    } else {
                        continue;
                    }
                }
            } elseif ($value instanceof IContainer) {
                $attributes[] = $key.'="'.(string)$value.'"';
            } else {
                $attributes[] = $key.'="'.$this->esc((string)$value).'"';
            }
        }

        if ($attributes = implode(' ', $attributes)) {
            $attributes = ' '.$attributes;
        }

        $output = '<'.$this->name.$attributes;

        if (!$this->closable) {
            $output .= ' /';
        }

        $output .= '>';
        return $output;
    }

    /**
     * Render closing </tag>
     */
    public function close(): string
    {
        if (!$this->closable) {
            return '';
        }

        return '</'.$this->name.'>';
    }


    /**
     * Render tag with inner content
     */
    public function renderWith(IContainer $content=null): ?IContainer
    {
        if ($this->closable && $content !== null) {
            $content = (string)$content;
        } elseif (!$this->closable && $this->renderEmpty) {
            return null;
        } else {
            $content = null;
        }

        return new Buffer($this->open().$content.$this->close());
    }

    /**
     * Render with raw string content
     */
    public function renderWithRaw(string $content=null): ?IContainer
    {
        return $this->renderWith(new Buffer($content));
    }


    /**
     * Set whether to render tag if no content
     */
    public function setRenderEmpty(bool $render): ITag
    {
        $this->renderEmpty = $render;
        return $this;
    }

    /**
     * Render tag if no content?
     */
    public function willRenderEmpty(): bool
    {
        return $this->renderEmpty;
    }


    /**
     * Set attribute value
     */
    public function setAttribute(string $key, $value): IAttributeContainer
    {
        $key = strtolower($key);

        if ($key == 'class') {
            return $this->setClasses($value);
        } elseif ($key == 'style') {
            return $this->setStyles($value);
        }

        if ($value === null) {
            return $this->removeAttribute($key);
        }

        if (!is_bool($value)) {
            $value = (string)$value;
        }

        $this->attributes[$key] = $value;
        return $this;
    }

    /**
     * Get attribute value
     */
    public function getAttribute(string $key)
    {
        $key = strtolower($key);

        if ($key == 'class') {
            return $this->getClasses();
        } elseif ($key == 'style') {
            return $this->getStyles();
        }

        return parent::getAttribute($key);
    }



    /**
     * Replace class list object
     */
    public function setClassList(ClassList $list): ITagDataContainer
    {
        $this->attributes['class'] = $list;
        return $this;
    }

    /**
     * Replace class list
     */
    public function setClasses(string ...$classes): ITagDataContainer
    {
        $this->getClasses()->clear()->add(...$classes);
        return $this;
    }

    /**
     * Add class set to list
     */
    public function addClasses(string ...$classes): ITagDataContainer
    {
        $this->getClasses()->add(...$classes);
        return $this;
    }

    /**
     * Get class list from attribute set
     */
    public function getClasses(): ClassList
    {
        if (!isset($this->attributes['class'])) {
            $this->attributes['class'] = new ClassList();
        }

        return $this->attributes['class'];
    }

    /**
     * Add class set to list
     */
    public function setClass(string ...$classes): ITagDataContainer
    {
        return $this->setClasses(...$classes);
    }

    /**
     * Get class list from attribute set
     */
    public function addClass(string ...$classes): ITagDataContainer
    {
        return $this->addClasses(...$classes);
    }

    /**
     * Remove set of classes from list
     */
    public function removeClass(string ...$classes): ITagDataContainer
    {
        $this->getClasses()->remove(...$classes);
        return $this;
    }

    /**
     * Does class list have any of these?
     */
    public function hasClass(string ...$classes): bool
    {
        return $this->getClasses()->has(...$classes);
    }

    /**
     * Does class list have ALL of these?
     */
    public function hasClasses(string ...$classes): bool
    {
        return $this->getClasses()->hasAll(...$classes);
    }

    /**
     * How many classes do we have?
     */
    public function countClasses(): int
    {
        return $this->getClasses()->count();
    }


    /**
     * Direct set id attribute
     */
    public function setId(?string $id): ITagDataContainer
    {
        if ($id === null) {
            $this->removeAttribute('id');
            return $this;
        }

        if (preg_match('/\s/', $id)) {
            throw \Glitch::EInvalidArgument('Invalid tag id: '.$id);
        }

        $this->setAttribute('id', $id);
        return $this;
    }

    /**
     * Get id attribute value
     */
    public function getId(): ?string
    {
        return $this->getAttribute('id');
    }


    /**
     * Toggle hidden attribute on/off
     */
    public function setHidden(bool $hidden): ITagDataContainer
    {
        $this->setAttribute('hidden', $hidden);
        return $this;
    }

    /**
     * Does this tag have hidden attr?
     */
    public function isHidden(): bool
    {
        return $this->hasAttribute('hidden');
    }

    /**
     * Set hidden attribute
     */
    public function hide(): ITagDataContainer
    {
        return $this->setAttribute('hidden', true);
    }

    /**
     * Remove hidden attribute
     */
    public function show(): ITagDataContainer
    {
        return $this->removeAttribute('hidden');
    }


    /**
     * Set title attribute
     */
    public function setTitle(?string $title): ITagDataContainer
    {
        return $this->setAttribute('title', $title);
    }

    /**
     * Get title attribute
     */
    public function getTitle(): ?string
    {
        return $this->getAttribute('title');
    }


    /**
     * Replace style list
     */
    public function setStyles(...$styles): ITagDataContainer
    {
        $this->getStyles()->clear()->import(...$styles);
        return $this;
    }

    /**
     * Merge style list
     */
    public function addStyles(...$styles): ITagDataContainer
    {
        $this->getStyles()->import(...$styles);
        return $this;
    }

    /**
     * Get style object
     */
    public function getStyles(): StyleList
    {
        if (!isset($this->attributes['style'])) {
            $this->attributes['style'] = new StyleList();
        }

        return $this->attributes['style'];
    }

    /**
     * Set a single style value
     */
    public function setStyle(string $key, ?string $value): ITagDataContainer
    {
        $styles = $this->getStyles();

        if ($value === null) {
            $styles->remove($key);
        } else {
            $styles->set($key, $value);
        }

        return $this;
    }

    /**
     * Get a single style value
     */
    public function getStyle(string $key): ?string
    {
        return $this->getStyles()->get($key);
    }

    /**
     * Remove set of styles
     */
    public function removeStyle(string ...$keys): ITagDataContainer
    {
        $this->getStyles()->remove(...$keys);
        return $this;
    }

    /**
     * List has any of these styles?
     */
    public function hasStyle(string ...$keys): bool
    {
        return $this->getStyles()->has(...$keys);
    }

    /**
     * List has ALL of these styles?
     */
    public function hasStyles(string ...$keys): bool
    {
        return $this->getStyles()->hasAll(...$keys);
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
     * Escape HTML
     */
    protected function esc(?string $value): ?string
    {
        return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
    }


    /**
     * Render to string
     */
    public function __toString(): string
    {
        return $this->open();
    }

    /**
     * Dump tag string
     */
    public function __debugInfo(): array
    {
        return [
            'tag' => $this->open().$this->close()
        ];
    }
}
