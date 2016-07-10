<?php
namespace Slion\Pack;

/**
 * Description of PackInterface
 *
 * @author andares
 */
interface PackInterface {
    public function encode($value);
    public function decode($data);
    public function setSettings(array $settings);
}
