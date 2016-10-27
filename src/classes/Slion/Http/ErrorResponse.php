<?php
namespace Slion\Http;

use Slion\Debugger;
use Slim\Http\{
    Response as Raw,
    Request as RawRequest
};
use Slion\Utils\Logger\Log;

/**
 *
 * @author andares
 */
class ErrorResponse extends Response {
    protected static $_default = [
    ];

    /**
     * @var int
     */
    protected $_http_code    = 200;

    /**
     *
     * @var string
     */
    protected $_template = 'error.phtml';

    /**
     * 错误词典名
     * @var string
     */
    protected static $message_dict = 'errors';

    /**
     *
     * @var \Throwable
     */
    protected $e;

    /**
     *
     * @var string
     */
    protected $message = '';

    /**
     *
     * @var int|string
     */
    protected $error_code = 0;

    /**
     *
     * @var string|null
     */
    protected $exc_file = null;

    public function __construct(Raw $raw, RawRequest $request, \Throwable $e) {
        parent::__construct($raw, $request);
        $this->e = $e;
        $this->by($e);
    }

    /**
     *
     * @param \Throwable $e
     */
    protected function by(\Throwable $e) {
        $this->error_code  = $e->getCode();

        // 生产环境隐去原生错误消息
        if (!is_prod() && $this->error_code === 0) {
            $this->message = $e->getMessage();
        } else {
            $message = $this->translateMessage($this->error_code);
            $this->message = ($message === $this->error_code) ?
                $e->getMessage() : $message;
        }
    }

    /**
     *
     * @param string|int $key
     * @return string
     */
    protected function translateMessage($key): string {
        return static::$message_dict ? \tr(static::$message_dict, $key) : "$key";
    }

    /**
     *
     * @param string $name
     * @param array $arguments
     * @return mixed
     */
    public function __call(string $name, array $arguments) {
        return $this->e->$name(...$arguments);
    }

    /**
     *
     * @param int|string $code
     */
    public function setErrorCode($code) {
        $this->error_code = $code;
    }

    /**
     *
     * @param string $message
     */
    public function setMessage(string $message) {
        $this->message = $message;
    }

    /**
     *
     * @param type $not_null
     */
    public function toArray(bool $not_null = false): array {
        $error = [
            'message'   => $this->message,
            'code'      => $this->error_code,
            'data'      => parent::toArray($not_null),
        ];
        return $error;
    }

    /**
     *
     * @return self
     */
    public function confirm(): self {
        $this->exc_file = Debugger::exceptionHandler($this->e);
        return parent::confirm();
    }

    /**
     *
     * @return array
     */
    protected function makeProtocol(): array {
        return [
            'result'    => null,
            'error'     => $this->toArray(),
            'channels'  => [],
        ];
    }

    public function toLog(string $catalog = 'send response'): Log {
        return parent::toLog($catalog)->setExcFile($this->exc_file);
    }

}
