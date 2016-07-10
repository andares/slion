<?php

namespace Slion;

/**
 * Description of Pack
 *
 * @author andares
 */
class Pack {
    /**
     *
     * @var Pack\PackInterface[]
     */
    private static $codecs = [];

    public static function encode($format, $value) {
        return self::getCodec($format)->encode($value);
    }

    public static function decode($format, $data) {
        return self::getCodec($format)->decode($data);
    }

    /**
     *
     * @param string $format
     * @return Pack\PackInterface
     */
    public static function getCodec($format) {
        if (!isset(self::$codecs[$format])) {
            $class = __CLASS__ . '\\' . ucfirst($format);
            self::$codecs[$format] = new $class();

            $pack_settings = \Slion::getSettings('pack');
            if (isset($pack_settings[$format])) {
                self::$codecs[$format]->setSettings($pack_settings[$format]);
            }
        }
        return self::$codecs[$format];
    }

    /**
     *
     * @param type $format
     * @param \Slion\Pack\PackInterface $codec
     */
    public static function setCodec($format, Pack\PackInterface $codec) {
        self::$codecs[$format] = $codec;
    }
}
