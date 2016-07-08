<?php

/*
 * license less
 */

namespace Slion\Http;

/**
 * Description of Controller
 *
 * @author andares
 */
abstract class Controller {
    public function __call($name, $arguments) {
        $action = ucfirst($name);
        $method = "action$action";

        try {
            if (!method_exists($this, $method)) {
                throw new \BadMethodCallException("method not exist $action@" . __CLASS__);
            }

            $request  = $this->getRequest($action);
            $response = $this->$method($request, ...$arguments);
            /* @var $response Response */
            $response->confirm();

            // 日志
            \dlog(">> send response: $response", 'info');
        } catch (\Exception $exc) {
            $response = new ErrorResponse();
            $response->by($exc);

            // 日志
            \Tracy\Debugger::exceptionHandler($exc);
        }

        return $response;
    }

    private function getRequest($action) {
        $class = get_called_class() . "\\{$action}Request";
        if (class_exists($class)) {
            $request = new $class($_REQUEST);
            $request->confirm();

            // 日志
            \dlog(">> receive request: $request", 'info');
        } else {
            $request = $_REQUEST;

            // 日志
            \dlog(">> receive request: " . json_encode($request), 'info');
        }

        return $request;
    }

}
