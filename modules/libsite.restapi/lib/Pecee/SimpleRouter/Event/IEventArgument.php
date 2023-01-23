<?php

namespace LibSite\RestApi\Pecee\SimpleRouter\Event;

use LibSite\RestApi\Pecee\Http\Request;
use LibSite\RestApi\Pecee\SimpleRouter\Router;

interface IEventArgument
{

    /**
     * Get event name
     *
     * @return string
     */
    public function getEventName(): string;

    /**
     * Set event name
     *
     * @param string $name
     */
    public function setEventName(string $name): void;

    /**
     * Get router instance
     *
     * @return Router
     */
    public function getRouter(): Router;

    /**
     * Get request instance
     *
     * @return Request
     */
    public function getRequest(): Request;

    /**
     * Get all event arguments
     *
     * @return array
     */
    public function getArguments(): array;

}