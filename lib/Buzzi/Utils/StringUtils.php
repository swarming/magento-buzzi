<?php

namespace Buzzi\Utils;

/**
 * @codeCoverageIgnore
 */
trait StringUtils
{
    /**
     * Convert kebab-case-strings to snake_case.
     *
     * @param  string $string
     * @return string
     */
    protected function kebabCaseToSnakeCase($string)
    {
        return str_replace('-', '_', $string);
    }

    /**
     * @param string $name
     * @return string
     */
    protected function camelize($name)
    {
        return implode('', array_map('ucfirst', explode('_', str_replace('-', '_', $name))));
    }

    /**
     * @param string $name
     * @return string
     */
    protected function underscore($name)
    {
        return strtolower(trim(preg_replace('/([A-Z]|[0-9]+)/', '_$1', $name), '_'));
    }
}
