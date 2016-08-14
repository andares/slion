<?php
namespace Slion\Http;

use Slion\Debugger;

/**
 *
 * @author andares
 */
class ErrorResponse extends Response {
    /**
     * 错误词典名
     * @var string
     */
    protected static $_message_dict = 'errors';

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
     *
     * @var \Throwable
     */
    private $e;

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

    public function __construct(Raw $raw, \Throwable $e) {
        parent::__construct($raw);
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
        return \tr(static::$_message_dict, $key);
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
    }

    /**
     *
     * @return self
     */
    public function confirm(): self {
        Debugger::exceptionHandler($this->e);
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

}
