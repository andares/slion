<?php
namespace Slion\Utils;

use Slion\Pack;
use Tracy\Logger as TracyLogger;

/**
 * Description of Logger
 *
 * @author andares
 */
class Logger extends TracyLogger {
    /**
     *
     * @var bool
     */
    public $enableBlueScreen = false;

    /**
     *
     * @param string $directory
     */
    protected function mkdir(string $directory) {
        if (!file_exists($directory)) {
            mkdir($directory, 0777, true);
        }
    }

    /**
     *
     * @param string $dir
     */
    public function setDirectory(string $dir) {
        $this->directory = $dir;
    }

    /**
     *
     * @param string $email
     */
    public function setEmail(string $email) {
        $this->email = $email;
    }

    /**
     *
     * @param mixed $object
     * @param string $priority
     * @return string|null
     */
    public function __invoke($object, string $priority = 'debug') {
        // 增加对数组的支持
        if (is_array($object)) {
            $object = Pack::encode('json', $object);
        }
        return $this->log($object, $priority);
    }

    /**
     *
     * @param string $name
     * @param array $arguments
     */
    public function __call(string $name, array $arguments) {
        $this($arguments[0] ?? '', $name);
    }

	/**
	 * @param  string|\Exception|\Throwable
	 * @return string
	 */
	protected function formatLogLine($message, $exception_file = NULL)
	{
        global $run;
        /* @var $run \Slion\Run */
        $line = [@date('[Y-m-d H:i:s]')];

        if ($message instanceof Logger\Log) {
            $ip_forward = $run->request->getHeader('HTTP_X_FORWARD_FOR')[0] ?? '';
            $message->setExtra([
                'ip'            => $run->environment->get('REMOTE_ADDR'),
                'ip_forward'    => $ip_forward,
                'source'        => _get_source(),
            ]);
            $line[] = "$message";
        } else {
            $line[] = "RID:" . $run->getId();
            $line[] = preg_replace('#\s*\r?\n\s*#', ' ',
                $this->formatMessage($message));
            $line[] = ' @  ' . \Tracy\Helpers::getSource();
            $exception_file && $line[] = ' @@  ' . $exception_file;
        }

		return implode(' ', $line);
	}

    /**
     *
     * @param \Throwable $exception
     * @return string
     */
	public function getExceptionFile($exception): string
	{
		$hash = substr(md5(preg_replace('~(Resource id #)\d+~', '$1', $exception)),
            0, 10);
		$dir  = strtr($this->directory . '/', '\\/',
            DIRECTORY_SEPARATOR . DIRECTORY_SEPARATOR) .
            @date('Y-m-d');
        if (!file_exists($dir)) {
            mkdir($dir, 0777, true);
        }
		return $dir . DIRECTORY_SEPARATOR . "exception--$hash.txt";
	}

    /**
     *
     * @param \Throwable $e
     * @param string $file
     * @return string
     */
	protected function logException($e, $file = null): string {
		$file = $file ?: $this->getExceptionFile($e);
        if (file_exists($file)) {
            return $file;
        }

        $content = '';
        file_put_contents($file, $this->makeExceptionLogContent($content, $e));
		return $file;
	}

    /**
     *
     * @param string $content
     * @param \Throwable $e
     * @return string
     */
    protected function makeExceptionLogContent(string $content,
        \Throwable $e):string {

        $content .= '- message: ' . $e->getMessage() . "\n" .
            '- code: ' . $e->getCode() . "\n" .
            '- file: ' . $e->getFile() . "\n" .
            '- line: ' . $e->getLine() . "\n" .
            "\n" .
            $e->getTraceAsString() . "\n\n+++++\n\n";
        $previous = $e->getPrevious();
        if ($previous) {
            return $this->makeExceptionLogContent($content, $previous);
        }
        return $content;
    }
}
