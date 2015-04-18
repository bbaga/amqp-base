<?php
namespace Amqp\Base\Builder;

class Base
{
    /**
     * Returns a name based on a definition
     *
     * @param array $definition The definition for grabbing the name
     *
     * @return string
     *
     * @throws Exception If components required in order to get the name are undefined
     */
    protected function getName(array $definition)
    {
        switch($definition['type']) {
            case "constant":
                $name = $definition['name'];
                break;
            case "static":
                if (!isset($definition['class'])) {
                    throw new Exception('Invalid Class', 400);
                }
                if (!class_exists($definition['class'], true)) {
                    throw new Exception('Class does not exist', 400);
                }
                $name = call_user_func(array($definition['class'], $definition['name']));
                if (!is_string($name) || empty($name)) {
                    throw new Exception('Invalid Name', 500);
                }
                break;
            case 'dynamic':
                if (!isset($definition['class'])) {
                    throw new Exception('Invalid Class', 400);
                }
                if (!class_exists($definition['class'], true)) {
                    throw new Exception('Class does not exist', 400);
                }
                $classInstance = new $definition['class'];
                $name = $classInstance->{$definition['name']}();
                if (!is_string($name) || empty($name)) {
                    throw new Exception('Invalid Name', 500);
                }
                break;
            case 'function':
                if (!function_exists($definition['name'])) {
                    throw new Exception('Invalid Function', 400);
                }
                $name = call_user_func($definition['name']);
                break;
            default:
                throw new Exception('Invalid Type', 400);
        }
        return $name;
    }

    /**
     * Builds a bitmask out of the elements present in array
     * All elements must have a constant associated with them
     *
     * @param array $constants The constants building the bitmask
     *
     * @return int
     */
    protected function buildBitmask(array $constants)
    {
        foreach ($constants as $constant) {
            if (!defined($constant)) {
                continue;
            }
            if (!isset($ret)) {
                $ret = constant($constant);
            } else {
                $ret |= constant($constant);
            }
        }

        if (!isset($ret)) {
            return AMQP_NOPARAM;
        } else {
            return $ret;
        }
    }

    /**
     * Returns a constant value if is defined
     *
     * @param string $constantName The name of the constant
     * @return mixed
     * @throws Exception If the constant is not defined
     */
    protected function getConstant($constantName)
    {
        if (defined($constantName)) {
            return constant($constantName);
        }

        throw new Exception('Invalid Constant', 500);
    }
}