<?php
namespace Slion\Utils;

use Tracy\Logger as TracyLogger;

/**
 * Description of Logger
 *
 * @author andares
 */
class Logger extends TracyLogger {
    public $enableBlueScreen = false;

    public function __construct($directory, $email = NULL, \Tracy\BlueScreen $blueScreen = NULL) {
        // 创建目录
        $this->mkdir($directory);

        parent::__construct($directory, $email, $blueScreen);
    }

    protected function mkdir($directory) {
        if (!file_exists($directory)) {
            mkdir($directory, 0777, true);
        }
    }

    public function __invoke($object, $priority = 'debug') {
        $this->log($object, $priority);
    }

    public function __call($name, $arguments) {
        $this->log($arguments[0] ?? '', $name);
    }

	/**
	 * @param  string|\Exception|\Throwable
	 * @return string
	 */
	protected function formatLogLine($message, $exceptionFile = NULL)
	{
        $line = [
			@date('[Y-m-d H:i:s]'),
			preg_replace('#\s*\r?\n\s*#', ' ', $this->formatMessage($message)),
			' @  ' . \Tracy\Helpers::getSource(),
			$exceptionFile ? ' @@  ' . basename($exceptionFile) : NULL,
		];
		if ($message instanceof \Exception || $message instanceof \Throwable) {
            $line[] = "\n" . $message->getTraceAsString();
        }
		return implode(' ', $line);
	}

    /**
     * 强烈建议不要开启 enableBlueScreen ，现有的功能已经足够调试。
     * 开启之后，需要同时关闭严格模式，否则当出现错误连续抛相同违例时，会发生违例 bluescreen 因文件名相同而报错的问题。
     *
     * @param type $exception
     * @param type $file
     * @return type
     */
	protected function logException($exception, $file = NULL) {
        return $this->enableBlueScreen ? parent::logException($exception, $file) : '';
	}
}
