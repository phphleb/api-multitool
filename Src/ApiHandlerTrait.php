<?php
declare(strict_types=1);

/** @author Foma Tuturov */

namespace Phphleb\ApiMultitool\Src;

use Psr\Log\LoggerInterface;

/**
 * Трeйт для преобразования данных в стандартизированные массивы.
 * Содержит распространённые виды преобразований, требуемые при работе с API.
 */
trait ApiHandlerTrait
{
    /** @internal */
    private array $apiBoxStandardDataSketch = ['data' => '%{data}', 'error' => '%{error}'];

    /** @internal */
    private bool $apiBoxDebug = false;

    /** @internal */
    private ?LoggerInterface $apiLogger = null;

    /**
     * Возвращает состояние активности режима отладки для вывода ошибок API.
     */
    public function isApiBoxDebug(): bool
    {
        return $this->apiBoxDebug;
    }

    /**
     * Возвращает состояние активности режима отладки для вывода ошибок API.
     */
    public function setApiBoxDebug(bool $value): void
    {
        $this->apiBoxDebug = $value;
    }

    /**
     * Установка собственной схемы ответа для API.
     */
    protected function setDataSketch(array $data): void
    {
        $this->apiBoxStandardDataSketch = $data;
    }

    /**
     * Назначает логгер для API.
     */
    protected function setApiLogger(LoggerInterface $logger): void
    {
        $this->apiLogger = $logger;
    }

    /*
     * Вывод кода состояния с возвращением сформированного ответа.
     * Код ошибки выводится только в сообщении ответа.
     */
    protected function present($data = ['success' => true], $code= 200, $error = null): array
    {
        array_walk_recursive($this->apiBoxStandardDataSketch, function (&$value, $key) use ($data, $error, $code) {
            if ($value === '%{data}') {
                $value = $data;
            } else if ($value === '%{error}') {
                $value = $error;
            } else if ($value === '%{error-message}') {
                $value = $this->getHttpCodeMessage($code);
            } else if ($value === '%{code}') {
                $value = $code;
            }
        });
        return $this->apiBoxStandardDataSketch;
    }

    /** Вывод кода состояния с возвращением преобразованной ошибки */
    protected function error($e)
    {
        if ($e instanceof \Throwable) {
            $error = $this->apiBoxDebug ? $e->getMessage() : "Error code " . $e->getCode();
            $detailError = $e->getMessage() . PHP_EOL . $e->getTraceAsString();
        } else {
            $error = $e;
        }
        /* Вывод ошибки в лог */
        $this->apiLogger and $this->apiLogger->error($detailError ?? $e && is_array($e) ? current($e) : $e);

        return $this->present(['success' => false], $error);
    }

    /** Поиск сообщения из распространённых ответов сервера. */
    protected function getHttpCodeMessage(int $code): string
    {
        switch ($code) {
            case 100:
                $txt = 'Continue';
                break;
            case 101:
                $txt = 'Switching Protocols';
                break;
            case 200:
                $txt = 'OK';
                break;
            case 201:
                $txt = 'Created';
                break;
            case 202:
                $txt = 'Accepted';
                break;
            case 203:
                $txt = 'Non-Authoritative Information';
                break;
            case 204:
                $txt = 'No Content';
                break;
            case 205:
                $txt = 'Reset Content';
                break;
            case 206:
                $txt = 'Partial Content';
                break;
            case 300:
                $txt = 'Multiple Choices';
                break;
            case 301:
                $txt = 'Moved Permanently';
                break;
            case 302:
                $txt = 'Moved Temporarily';
                break;
            case 303:
                $txt = 'See Other';
                break;
            case 304:
                $txt = 'Not Modified';
                break;
            case 305:
                $txt = 'Use Proxy';
                break;
            case 400:
                $txt = 'Bad Request';
                break;
            case 401:
                $txt = 'Unauthorized';
                break;
            case 402:
                $txt = 'Payment Required';
                break;
            case 403:
                $txt = 'Forbidden';
                break;
            case 404:
                $txt = 'Not Found';
                break;
            case 405:
                $txt = 'Method Not Allowed';
                break;
            case 406:
                $txt = 'Not Acceptable';
                break;
            case 407:
                $txt = 'Proxy Authentication Required';
                break;
            case 408:
                $txt = 'Request Time-out';
                break;
            case 409:
                $txt = 'Conflict';
                break;
            case 410:
                $txt = 'Gone';
                break;
            case 411:
                $txt = 'Length Required';
                break;
            case 412:
                $txt = 'Precondition Failed';
                break;
            case 413:
                $txt = 'Request Entity Too Large';
                break;
            case 414:
                $txt = 'Request-URI Too Large';
                break;
            case 415:
                $txt = 'Unsupported Media Type';
                break;
            case 500:
                $txt = 'Internal Server Error';
                break;
            case 501:
                $txt = 'Not Implemented';
                break;
            case 502:
                $txt = 'Bad Gateway';
                break;
            case 503:
                $txt = 'Service Unavailable';
                break;
            case 504:
                $txt = 'Gateway Time-out';
                break;
            case 505:
                $txt = 'HTTP Version not supported';
                break;
            default:
                $txt = ('Http status code "' . htmlentities((string)$code) . '"');
                break;
        }
        return $txt;
    }
}

