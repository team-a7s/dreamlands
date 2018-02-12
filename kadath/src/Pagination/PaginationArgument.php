<?php

declare(strict_types=1);

namespace Kadath\Pagination;

use GraphQL\Error\ClientAware;
use Kadath\Exceptions\KadathException;

class PaginationArgument
{
    const DIRECTION_FORWARD = 1;
    const DIRECTION_BACKWARD = 2;

    protected $cursor;
    protected $size;
    protected $direction;

    public function __construct(int $size, int $direction, string $cursor = '')
    {
        if ($size == 0) {
            throw KadathException::badRequest('zero size');
        }

        if ($direction !== self::DIRECTION_BACKWARD && $direction !== self::DIRECTION_FORWARD) {
            throw KadathException::badRequest('bad direction');
        }

        $this->cursor = $cursor;
        $this->size = $size;
        $this->direction = $direction;
    }

    /**
     * @return string
     */
    public function getCursor(): string
    {
        return $this->cursor;
    }

    /**
     * @return int
     */
    public function getSize(): int
    {
        return $this->size;
    }

    /**
     * @return int
     */
    public function getDirection(): int
    {
        return $this->direction;
    }

    public function isForward()
    {
        return $this->direction === self::DIRECTION_FORWARD;
    }

    public static function fromArray($arg)
    {
        try {
            if (!empty($arg['first'])) {
                if (!empty($arg['last']) || !empty($arg['before'])) {
                    throw KadathException::badRequest('bad direction');
                }
                return new static($arg['first'], static::DIRECTION_FORWARD, $arg['after'] ?? '');
            }
            if (!empty($arg['last'])) {
                if (!empty($arg['first']) || !empty($arg['after'])) {
                    throw KadathException::badRequest('bad direction');
                }
                return new static($arg['last'], static::DIRECTION_BACKWARD, $arg['before'] ?? '');
            }
        } catch (ClientAware $e) {
            throw $e;
        } catch (\Throwable $e) {
            throw KadathException::badRequest('bad pagination', $e);
        }

        throw KadathException::badRequest('pagination args not found');
    }
}
