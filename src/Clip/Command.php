<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
declare(strict_types=1);
namespace Df\Clip;

use Df;

use Df\Clip\IShell;
use Df\Clip\Command\IArgument;
use Df\Clip\Command\Argument;
use Df\Clip\Command\IRequest;

class Command implements ICommand
{
    protected $path;
    protected $help;

    protected $arguments = [];

    /**
     * Init with path
     */
    public function __construct(string $path)
    {
        $this->setPath($path);
    }

    /**
     * Set task path
     */
    public function setPath(string $path): ICommand
    {
        $this->path = $path;
        return $this;
    }

    /**
     * Get task path
     */
    public function getPath(): string
    {
        return $this->path;
    }


    /**
     * Set help info
     */
    public function setHelp(?string $help): ICommand
    {
        $this->help = $help;
        return $this;
    }

    /**
     * Get help info
     */
    public function getHelp(): ?string
    {
        return $this->help;
    }



    /**
     * Add a single argument to the queue
     */
    public function addArgument(string $name, string $description, callable $setup=null): ICommand
    {
        if (isset($this->arguments[$name])) {
            throw Df\Error::ELogic(
                'Named argument "'.$name.'" has already been defined'
            );
        }

        $argument = new Argument($name, $description);

        if ($setup) {
            $setup($argument, $this);
        }

        return $this->setArgument($argument);
    }

    /**
     * Push an argument to the queue
     */
    public function setArgument(IArgument $arg): ICommand
    {
        $this->arguments[$arg->getName()] = $arg;
        return $this;
    }

    /**
     * Lookup a named argument
     */
    public function getArgument(string $name): ?IArgument
    {
        return $this->arguments[$name] ?? null;
    }

    /**
     * Get list of arguments
     */
    public function getArguments(): array
    {
        return $this->arguments;
    }

    /**
     * Remove an argument from the queue
     */
    public function removeArgument(string $name): ICommand
    {
        unset($this->arguments[$name]);
        return $this;
    }

    /**
     * Remove all arguments from queue
     */
    public function clearArguments(): ICommand
    {
        $this->arguments = [];
        return $this;
    }


    /**
     * Convert request params to list of args
     */
    public function apply(IRequest $request): array
    {
        // Sort arguments
        $args = $opts = $output = [];
        $lastIsList = $lastIsOptional = false;

        foreach ($this->arguments as $arg) {
            if ($arg->isNamed()) {
                $opts[$arg->getName()] = $arg;

                if (null !== ($shortcut = $arg->getShortcut())) {
                    $opts[$shortcut] = $arg;
                }
            } else {
                if ($lastIsList) {
                    throw Df\Error::ELogic(
                        'List arguments must come last in the command definition'
                    );
                }

                $args[$arg->getName()] = $arg;

                if ($arg->isList()) {
                    $lastIsList = true;
                }

                if (!$arg->isOptional()) {
                    if ($lastIsOptional) {
                        throw Df\Error::ELogic(
                            'Optional arguments cannot appear before required arguments'
                        );
                    }

                    $lastIsOptional = false;
                } else {
                    $lastIsOptional = true;
                }
            }
        }

        $params = $request->getCommandParams();

        while (!empty($params)) {
            $param = array_shift($params);

            if (substr($param, 0, 1) == '-') {
                $isShortcut = substr($param, 0, 2) !== '--';
                $parts = explode('=', ltrim($param, '-'));
                $name = array_shift($parts);

                if (!$arg = ($opts[$name] ?? null)) {
                    throw Df\Error::EUnexpectedValue(
                        'Unexpected option: '.$name
                    );
                }

                if ($isShortcut) {
                    if ($arg->isBoolean()) {
                        $param = true;
                    } else {
                        $param = array_shift($params);
                    }
                } else {
                    $param = array_shift($parts);

                    if ($param === null) {
                        $param = true;
                    }
                }


                if (!$arg->isList()) {
                    unset($opts[$arg->getName()]);

                    if (null !== ($shortcut = $arg->getShortcut())) {
                        unset($opts[$shortcut]);
                    }
                }
            } else {
                if (!$arg = array_shift($args)) {
                    throw Df\Error::EUnexpectedValue(
                        'Unexpected argument: '.$param
                    );
                }

                if ($arg->isList()) {
                    array_unshift($args, $arg);
                }
            }

            $this->validate($arg, $param, $output);
        }

        foreach ($args as $arg) {
            $this->validate($arg, null, $output);
        }

        foreach ($opts as $arg) {
            $this->validate($arg, null, $output);
        }

        return $output;
    }

    private function validate(IArgument $arg, $param, array &$output)
    {
        $name = $arg->getName();

        if ($arg->isList()) {
            if ($param === null) {
                if (!isset($output[$name])) {
                    if ($arg->isOptional()) {
                        if (null !== ($default = $arg->getDefaultValue())) {
                            $output[$name] = [$default];
                        } else {
                            $output[$name] = null;
                        }
                    } else {
                        throw Df\Error::EUnexpectedValue(
                            'No list values defined for argument: '.$this->name
                        );
                    }
                }
            } else {
                $output[$name][] = $arg->validate($param);
            }
        } else {
            $output[$name] = $arg->validate($param);
        }
    }


    /**
     * Render help text
     */
    public function renderHelp(IShell $shell): void
    {
        $shell->writeLine();
        $shell->render($this->path, '+yellow|bold');
        $shell->render(' - ', '+');
        $shell->render($this->help, 'bold');

        $shell->writeLine();

        foreach ($this->arguments as $arg) {
            if ($arg->isNamed()) {
                continue;
            }

            $this->renderArg($shell, $arg);
        }

        foreach ($this->arguments as $arg) {
            if (!$arg->isNamed()) {
                continue;
            }

            $this->renderArg($shell, $arg);
        }
    }

    private function renderArg(IShell $shell, IArgument $arg)
    {
        if (!$arg->isNamed()) {
            $shell->render($arg->getName(), '+cyan|bold');

            if ($default = $arg->getDefaultValue()) {
                $shell->render(' [=', '+');
                $shell->render($default, '+green');
                $shell->render(']', '+');
            }

            $shell->writeLine();
        } else {
            $name = '--'.$arg->getName();

            if (null !== ($shortcut = $arg->getShortcut())) {
                $name .= ' | -'.$shortcut;
            }

            $shell->render($name, '+magenta|bold');

            if (!$arg->isBoolean()) {
                if ($pattern = $arg->getPattern()) {
                    $shell->render('<', '+');
                    $shell->render($pattern, '+yellow');
                    $shell->render('>', '+');
                } elseif ($default = $arg->getDefaultValue()) {
                    $shell->render('[=', '+');
                    $shell->render($default, '+green');
                    $shell->render(']', '+');
                } else {
                    $shell->render('<', '+');
                    $shell->render('value', '+cyan');
                    $shell->render('>', '+');
                }
            }

            $shell->writeLine();
        }

        $shell->render($arg->getDescription(), '>white|bold');
        $shell->writeLine();
    }
}