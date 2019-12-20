<?php

namespace Charcoal\Object;

/**
 * Defines a route to an object implementing {@see \Charcoal\Object\RoutableInterface}.
 *
 * {@see \Charcoal\Object\ObjectRoute} for a basic implementation.
 */
interface ObjectRouteInterface
{
    /**
     * Determine if the current slug is unique.
     *
     * @return boolean
     */
    public function isSlugUnique();
}
