<?php
namespace Slion\Utils;

use Tracy\Logger as TracyLogger;

/**
 * Description of Logger
 *
 * @author andares
 */
class Logger extends TracyLogger {
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

    public function __invoke($message, $priority = 'debug') {
        $this->log($message, $priority);
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
		return implode(' ', [
			@date('[Y-m-d H:i:s]'),
			preg_replace('#\s*\r?\n\s*#', ' ', $this->formatMessage($message)),
			' @  ' . Helpers::getSource(),
			$exceptionFile ? ' @@  ' . basename($exceptionFile) : NULL,
		]);
	}


}
