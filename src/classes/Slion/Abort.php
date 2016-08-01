<?php

namespace Slion;

/**
 * Description of Abort
 *
 * @author andares
 */
class Abort extends \Error {
    use Components\Collection;

    /**
     *
     * @var \Throwable
     */
    protected $e;

    public function __construct(\Throwable $e) {
        parent::__construct($e->getMessage(), $e->getCode(), $e);

        $this->e = $e;
    }

    public function __invoke(): \Throwable {
        return $this->e;
    }

	public function __toString(): string {
        return "$this->e";
    }
}
