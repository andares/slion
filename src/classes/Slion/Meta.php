<?php
namespace Slion;

/**
 *
 * @author andares
 */
abstract class Meta implements \IteratorAggregate, \ArrayAccess, \Serializable, \JsonSerializable {
    use Meta\Base, Meta\Access, Meta\Serializable, Meta\Json;

    /**
     * 构造器
     * @param array $data
     */
    public function __construct(array $data = null) {
        $data && $this->fill($data);
    }
}
