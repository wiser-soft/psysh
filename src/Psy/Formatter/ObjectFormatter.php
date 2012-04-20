<?php

/*
 * This file is part of PsySH
 *
 * (c) 2012 Justin Hileman
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Psy\Formatter;

/**
 * A pretty-printer for object references..
 */
class ObjectFormatter
{
    public static function format($obj)
    {
        $class = new \ReflectionClass($obj);
        $props = self::getProperties($obj, $class);

        return sprintf('%s %s', self::formatRef($obj), self::formatProperties($props));
    }

    public static function formatRef($obj)
    {
        return sprintf('<%s #%s>', get_class($obj), spl_object_hash($obj));
    }

    private static function formatProperties($props)
    {
        if (empty($props)) {
            return '{}';
        }

        $formatted = array();
        foreach ($props as $name => $val) {
            $formatted[] = sprintf('%s: %s', $name, self::formatVal($val));
        }

        $template = sprintf('{%s%s%%s%s   }', PHP_EOL, str_repeat(' ', 7), PHP_EOL);
        $glue     = sprintf(',%s%s', PHP_EOL, str_repeat(' ', 7));

        return sprintf($template, implode($glue, $formatted));
    }

    private static function getProperties($obj, \ReflectionClass $class)
    {
        $props = array();
        foreach ($class->getProperties(\ReflectionProperty::IS_PUBLIC) as $prop) {
            $props[$prop->getName()] = $prop->getValue($obj);
        }

        foreach (array_keys(json_decode(json_encode($obj), true)) as $prop) {
            if (!isset($props[$prop])) {
                $props[$prop] = $obj->$prop;
            }
        }

        return $props;
    }

    private static function formatVal($val)
    {
        if (is_object($val)) {
            return self::formatRef($val);
        } elseif (is_array($val)) {
            return sprintf('Array(%d)', count($val));
        } else {
            return json_encode($val);
        }
    }
}
