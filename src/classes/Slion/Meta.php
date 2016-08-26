<?php
namespace Slion;

/**
 *
 * @author andares
 */
abstract class Meta extends Meta\Base implements \ArrayAccess, \Serializable, \JsonSerializable {
    use Meta\Access, Meta\Serializable, Meta\Json;

    /**
     * 构造器
     * @param array|Meta\Base $data
     */
    public function __construct($data = null) {
        $data && $this->fill($data);
    }
}
