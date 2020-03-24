<?php

/*
 * This file is part of the Composer configuration reader package.
 *
 * Copyright © 2016 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Eloquent\Composer\Configuration\Element;

/**
 * An abstract base class for repositories.
 */
abstract class AbstractRepository implements RepositoryInterface
{
    /**
     * Construct a new repository.
     *
     * @param string                   $type    The repository type.
     * @param array<string,mixed>|null $options The repository options.
     * @param mixed                    $rawData The raw data describing the repository.
     */
    public function __construct(
        $type,
        array $options = null,
        $rawData = null
    ) {
        if (null === $options) {
            $options = array();
        }

        $this->type = $type;
        $this->options = $options;
        $this->rawData = $rawData;
    }

    /**
     * Get the repository type.
     *
     * @return string The repository type.
     */
    public function type()
    {
        return $this->type;
    }

    /**
     * Get the repository options.
     *
     * @return array<string,mixed> The repository options.
     */
    public function options()
    {
        return $this->options;
    }

    /**
     * Get the raw repository data.
     *
     * @return mixed The raw repository data.
     */
    public function rawData()
    {
        return $this->rawData;
    }

    private $type;
    private $options;
    private $rawData;
}
