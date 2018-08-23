<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
declare(strict_types=1);
namespace Df\Opal\Native;

use Df;
use Df\Opal\Query\IComposedSource;

class Source implements IComposedSource
{
    protected $name;
    protected $data;
    protected $fieldNames;

    /**
     * Init with data
     */
    public function __construct(string $name, array $data)
    {
        $this->data = $data;

        if (preg_match('/[^a-zA-Z0-9_-]/', $name)) {
            throw Df\Error::EInvalidArgument('Source name must only contain alphanumerics, - and _');
        }

        $this->name = $name;
    }

    /**
     * Get name
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Get query source id
     */
    public function getQuerySourceId(): string
    {
        return 'native://'.$this->name.'#'.uniqid();
    }

    /**
     * Get default alias
     */
    public function getDefaultQueryAlias(): string
    {
        return $this->name;
    }

    /**
     * Description
     */
    public function getFieldNames(): array
    {
        if ($this->fieldNames === null) {
            if (!isset($this->data[0])) {
                $this->fieldNames = [];
            } else {
                $this->fieldNames = array_keys($this->data[0]);
            }
        }

        return $this->fieldNames;
    }
}
