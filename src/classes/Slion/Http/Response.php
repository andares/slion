<?php
namespace Slion\Http;

use Slion\Meta;
use Slim\Http\Response as Raw;
use Slim\Collection;
use Slion\Utils\Logger\Log;

/**
 * Http响应
 *
 * @author andares
 */
class Response extends Meta\Base
    implements \ArrayAccess, \Serializable, \JsonSerializable {
    use Meta\Access, Meta\Serializable, Meta\Json;

    protected static $_default = [
        'ok'    => 1,
    ];

    protected $_http_code    = 200;

    protected $_http_headers = [
    ];

    /**
     *
     * @var string
     */
    protected $template = 'index.phtml';

    /**
     *
     * @var array
     */
    protected $channels = [];

    /**
     *
     * @var Raw
     */
    protected $raw;

    /**
     *
     * @param Raw $raw
     */
    public function __construct(Raw $raw) {
        $this->raw = $raw;
    }

    /**
     *
     * @return Raw
     */
    public function raw(): Raw {
        return $this->raw;
    }

    /**
     *
     * @param string $template
     */
    public function setTemplate(string $template) {
        $this->template = $template;
    }

    /**
     *
     * @return string
     */
    public function getTemplate(): string {
        return $this->template;
    }

    /**
     *
     * @param string $name
     * @param Collection $data
     */
    public function setChannelData(string $name, Collection $data) {
        $this->channels[$name] = $data;
    }

    /**
     *
     * @param int $code
     */
    public function setHttpCode(int $code = 200) {
        $this->_http_code = $code;
    }

    /**
     *
     * @return int
     */
    public function getHttpCode(): int {
        return $this->_http_code;
    }

    /**
     *
     * @param array $headers
     * @param bool $reset
     */
    public function setHeaders(array $headers = [], bool $reset = false) {
        $reset && $this->_http_headers = [];

        foreach ($headers as $name => $value) {
            $this->_http_headers[$name] = $value;
        }
    }

    /**
     *
     * @param Raw $response
     * @return Raw
     */
    public function regress(): Raw {
        $response = $this->raw;
        if ($this->_http_headers) {
            foreach ($this->_http_headers as $name => $value) {
                $response = $response->withHeader($name, $value);
            }
        }
        return $response->withStatus($this->_http_code);
    }

    /**
     *
     * @return array
     */
    public function jsonSerialize(): array {
        return $this->makeProtocol();
    }

    /**
     *
     * @return array
     */
    protected function makeProtocol(): array {
        return [
            'result'    => $this->toArray(),
            'error'     => null,
            'channels'  => $this->makeChannelArray(),
        ];
    }

    /**
     *
     * @return array
     */
    protected function makeChannelArray(): array {
        $result = [];
        foreach ($this->channels as $name => $data) {
            /* @var $data Collection */
            foreach ($data as $key => $row) {
                if (is_object($row)) {
                    if ($row instanceof \Slion\Meta\Base) {
                        $result[$name][$key] = $row->confirm()->toArray();
                    } else {
                        $result[$name][$key] = method_exists($row, 'toArray') ?
                            $row->toArray() : $row;
                    }
                } elseif (is_callable($row)) {
                    $result[$name][$key] = $row();
                } else {
                    $result[$name][$key] = $row;
                }
            }
        }
        return $result;
    }

    /**
     *
     * @return Log
     */
    public function toLog(string $catalog = 'send response'): Log {
        $log = new Log($catalog);
        $log->http_code = $this->getHttpCode();
        $log->data      = $this->makeProtocol();
        return $log;
    }
}
