<?php
declare(strict_types = 1);

/** @author Foma Tuturov */

namespace Phphleb\ApiMultitool\Src;

/**
 * Трейт для разбора произвольного массива и проверки его на валидность с условиями из другого массива.
 * Предполагается, что это данные пришедшие из HTTP-запроса или часть его. Несмотря на то, что происхождение
 * массивов не принципиально, формат валидации больше всего подходит именно к данным входящего запроса.
 */
trait ApiRequestDataManagerTrait
{
    /** @internal */
    private array $apiBoxErrorCells = [];

    /** @internal */
    private array $apiBoxErrorMessages = [];

    /** @internal */
    private bool $apiBoxReturnFirst = true;

    /**
     * Сообщения об ошибках.
     * Если понадобились другие описания, то нужно зменить значения, оставив ключи.
     * @internal
     */
    private array $apiBoxErrorsList = [
        'error.empty_name' => 'Invalid request data name: %s',
        'error.wrong_value_array_format' => 'Wrong array format for field %s',
        'error.wrong_value_format' => 'Wrong value format for field %s',
        'error.wrong_value_max_format' => 'The value is greater than the maximum allowed for the field %s',
        'error.wrong_value_min_format' => 'The value is less than the minimum allowed for the field %s',
        'error.wrong_value_length' => 'Wrong value length for field %s',
        'error.wrong_value_type' => 'Wrong type %s for field %s',
        'error.wrong_value_enum' => 'Value not found in enumerated values for field %s'
    ];

    /**
     * Возвращает список назначенных сообщений об ошибках валидации.
     */
    public function getApiBoxErrorsList(): array
    {
        return $this->apiBoxErrorsList;
    }

    /**
     * Устанавливает список ответов для ошибок.
     */
    public function setApiBoxErrorsList(array $apiBoxErrorsList): void
    {
        $this->apiBoxErrorsList = $apiBoxErrorsList;
    }

    /**
     * Устанавливает значение, которое определяет, будет ли валидация прерываться при первой найденной ошибке.
     */
    protected function setReturnFirst(bool $value): void
    {
        $this->apiBoxReturnFirst = $value;
    }

    /**
     * Информирует об значении условия, будет ли валидация прерываться при первой найденной ошибке.
     */
    protected function getReturnFirst(): bool
    {
        return $this->apiBoxReturnFirst;
    }

    /**
     * Перечень названий полей, которые не прошли валидацию, обновляется после выполнения $this->check(...).
     * Если пустой массив, то ошибок нет.
     */
    protected function getErrorCells(): array
    {
        return $this->apiBoxErrorCells;
    }

    /**
     * Перечень сообщений об ошибках, которые не прошли валидацию, обновляется после выполнения $this->check(...).
     * Если пустой массив, то ошибок нет.
     */
    protected function getErrorMessage(): array
    {
        return $this->apiBoxErrorMessages;
    }

    /**
     * Возвращает более удобный ответ, чем у $this->getErrorMessage(): если ошибка одна, то возвращается ошибка,
     * если несколько - массив с ошибками, если пустой, то null, обновляется после выполнения $this->check(...)
     * @return array|string|null
     */
    protected function getFormattedError()
    {
        return count($this->apiBoxErrorMessages) === 1 ? current($this->apiBoxErrorMessages) : ($this->apiBoxErrorMessages ?: null);
    }

    /**
     * Сверка массива с правилами валидации и массива с проверочными данными.
     * @param array $received - проверяемый массив.
     * @param array $rules - массив с правилами валидации.
     * @param string $prefixName - добавляет к выводимым названиям полей префикс. Это может быть нужно,
     * если проверке подверглась только часть входящих данных и нужно указать какая именно от корня массива,
     * или входящее значение представляет собой массив одинаковых данных и при переборе его указывается ключ
     * как префикс, чтобы отобразить при какой итерации проверяемое значение не прошло валидацию.
     * @param bool|null $returnFirst - нужно ли прерывать сверку при первой найденной ошибке, в противном
     * случае будут проверены все остальные поля и выбран список всех ошибок. Является первостепенной,
     * будучи назначенной, по отношению к $this->apiBoxReturnFirst.
     * @param bool $clearPreviousResult - системная переменная, указывающая на то, стоит ли очищать предыдущий результат работы функции.
     *
     * Этот метод производит верификацию массива на соответствие правил из второго массива,
     * при этом ДАННЫЕ НЕ БУДУТ ИЗМЕНЕНЫ, только сформирован перечень ошибок в полях, если ошибки есть.
     * Проверяется не только рабочий массив, но и валидность составленных условий в массиве с правилами.
     * Пример:
     * $isCorrectInputData = $this->check([
     *   'id' => 'required|type:int|min:1',
     *   'login' => 'required|type:string|minlen:3|maxlen:255'.
     *   'age' => 'type:float|min:0|max:122.3',
     *   'active' => 'required|enum:0,1'
     * ], $inputData);
     *
     * Если массив многоуровневый, то для названия нужно указать проверяемую вложенность в квадратных скобках.
     * Например: '[data][1]['2'][id]' будет найдено в array(['data'][1]['2']['id']). Из примера видно, для того,
     * чтобы задать числовому значению строчный тип - нужно обрамить его одинарными кавычками. По умолчанию название
     * считается без вложенности.
     *
     * Если вы указали два значения типа, например 'type:int,string', то диапазоны значений можно указать раздельным:
     * 'type:int,string|min:0|max:1000|minlen:3|maxlen:255|regex:[0-9]+'. В примере для числового типа будет проверено вхождение значения
     * в диапазоне от 0 до 1000, для строкового - допустимая символьная длина 3-255 символов и по регулярному выражению проверяется, что только цифры.
     * Используйте регулярные выражения только в крайнем случае.
     *
     * В примере будет проверена правильность составления правил валидации:
     * `required` - является ли это поле обязательным, внимание! параметр `required` должен быть только в самом начале правил.
     * `type` - один или несколько возможных типов через запятую, сверяется поочередно с типом пришедшего значения.
     *          Возможные типы 'string', 'float', 'int', 'regex', 'fullregex', 'bool', 'null' (null) или отсутствие значения (пустая строка) 'void'.
     *          `regex` и `fullregex` - регулярное выражение для проверки строковых значений соответственно без обрамления и полное.
     * `min` - минимальное значение для числовых значений.
     * `max` - максимальное значение для числовых значений.
     * `minlen` - минимальное значение для строковых значений.
     * `maxlen` - максимальное значение для строковых значений.
     * `enum` - перечисление обязательных значений. Например, правило 'enum:number 1,3,ok' проверит соответствие проверяемого значения
     *          на нестрогое равенство одному из перечисленных через запятую. Пройдут значения 'number 1','3',3 и 'ok'.
     * Пробелы не допускаются, только в перечислении параметров enum в самих параметрах (запятая разделитель).
     *
     * Проверка массивов с перечислением подобна вышеуказанной, только в правилах нужно указать поля перечисления в массиве:
     * $isCorrectInputData = $this->check([
     *    // Необязательное поле, массив с перечислением (в каждом проверяется два поля).
     *   'users' => ['id' => 'required|type:int', 'name' => 'required|type:string'],
     *    // Обязательное поле, массив с перечислением (в каждом проверяется три поля).
     *   'images' => ['required', ['id' => 'required|type:int', 'width' => 'required|type:int', 'height' => 'required|type:int']]
     * ], $inputData);
     *
     * @return bool - успешно или неуспешно прошли проверки.
     * @throws \ErrorException
     */
    protected function check(array $received, array $rules, string $prefixName = '', ?bool $returnFirst = null, bool $clearPreviousResult = true): bool
    {
        if ($clearPreviousResult) {
            $this->apiBoxErrorCells = [];
            $this->apiBoxErrorMessages = [];
        }
        $returnFirst = is_null($returnFirst) ? $this->apiBoxReturnFirst : $returnFirst;

        foreach ($rules as $name => $value) {
            if (is_string($value)) {
                $rulesValue = trim($value, ' |');
                $required = strpos($rulesValue, 'required') === 0;
            } else {
                $rulesValue = $value;
                $required = false;
                if (is_array($rulesValue)) {
                    if (count($rulesValue) === 2 && $rulesValue[0] === 'required' && is_array($rulesValue[1])) {
                        $required = true;
                        $rulesValue = $rulesValue[1];
                    }
                }
            }
            $inputValue = null;
            // Проверка на наличие названия.
            $search = true;
            if (!isset($received[$name])) { // Непрямое совпадение
                $lvl = explode('][', trim($name, ']['));
                foreach ($lvl as $k => $l) {
                    if (!is_numeric(trim($l, '\''))) {
                        $lvl[$k] = (string)trim($l, '\'');
                    } else {
                        $lvl[$k] = is_int($l) ? (int)$l : (float)$l;
                    }
                }

                // switch здесь использован для быстродействия, поиск на уровень вложенности до 10-го.
                switch (count($lvl)) {
                    case 1:
                        if ($search = array_key_exists($lvl[0], $received)) {
                            $inputValue = $received[$lvl[0]];
                        }
                        break;
                    case 2:
                        if ($search = array_key_exists($lvl[1], $received[$lvl[0]] ?? [])) {
                            $inputValue = $received[$lvl[0]][$lvl[1]];
                        }
                        break;
                    case 3:
                        if ($search = array_key_exists($lvl[2], $received[$lvl[0]][$lvl[1]] ?? [])) {
                            $inputValue = $received[$lvl[0]][$lvl[1]][$lvl[2]];
                        }
                        break;
                    case 4:
                        if ($search = array_key_exists($lvl[3], $received[$lvl[0]][$lvl[1]][$lvl[2]] ?? [])) {
                            $inputValue = $received[$lvl[0]][$lvl[1]][$lvl[2]][$lvl[3]];
                        }
                        break;
                    case 5:
                        if ($search = array_key_exists($lvl[4], $received[$lvl[0]][$lvl[1]][$lvl[2]][$lvl[3]] ?? [])) {
                            $inputValue = $received[$lvl[0]][$lvl[1]][$lvl[2]][$lvl[3]][$lvl[4]];
                        }
                        break;
                    case 6:
                        if ($search = array_key_exists($lvl[5], $received[$lvl[0]][$lvl[1]][$lvl[2]][$lvl[3]][$lvl[4]] ?? [])) {
                            $inputValue = $received[$lvl[0]][$lvl[1]][$lvl[2]][$lvl[3]][$lvl[4]][$lvl[5]];
                        }
                        break;
                    case 7:
                        if ($search = array_key_exists($lvl[6], $received[$lvl[0]][$lvl[1]][$lvl[2]][$lvl[3]][$lvl[4]][$lvl[5]] ?? [])) {
                            $inputValue = $received[$lvl[0]][$lvl[1]][$lvl[2]][$lvl[3]][$lvl[4]][$lvl[5]][$lvl[6]];
                        }
                        break;
                    case 8:
                        if ($search = array_key_exists($lvl[7], $received[$lvl[0]][$lvl[1]][$lvl[2]][$lvl[3]][$lvl[4]][$lvl[5]][$lvl[6]] ?? [])) {
                            $inputValue = $received[$lvl[0]][$lvl[1]][$lvl[2]][$lvl[3]][$lvl[4]][$lvl[5]][$lvl[6]][$lvl[7]];
                        }
                        break;
                    case 9:
                        if ($search = array_key_exists($lvl[8], $received[$lvl[0]][$lvl[1]][$lvl[2]][$lvl[3]][$lvl[4]][$lvl[5]][$lvl[6]][$lvl[7]] ?? [])) {
                            $inputValue = $received[$lvl[0]][$lvl[1]][$lvl[2]][$lvl[3]][$lvl[4]][$lvl[5]][$lvl[6]][$lvl[7]][$lvl[8]];
                        }
                        break;
                    case 10:
                        if ($search = array_key_exists($lvl[9], $received[$lvl[0]][$lvl[1]][$lvl[2]][$lvl[3]][$lvl[4]][$lvl[5]][$lvl[6]][$lvl[7]][$lvl[8]] ?? [])) {
                            $inputValue = $received[$lvl[0]][$lvl[1]][$lvl[2]][$lvl[3]][$lvl[4]][$lvl[5]][$lvl[6]][$lvl[7]][$lvl[8]][$lvl[9]];
                        }
                        break;
                    default:
                        $search = false;
                }
                if ($search === false && $required) {
                    $this->apiBoxErrorCells[] = $name;
                    $this->apiBoxErrorMessages[$name] = sprintf($this->apiBoxErrorsList['error.empty_name'], $prefixName . $name);
                    if ($returnFirst) {
                        return false;
                    }
                    continue;
                }
            }

            // Проверка правильности значения, если найдено - нужно проверить, даже если не обязательное поле.
            if ($search === true) {
                $inputValue = $inputValue ?? $received[$name];
                // Если нужен перебор массива
                if (is_array($rulesValue)) {
                    if (count($rulesValue) && is_array($inputValue)) {
                        foreach($inputValue as $key => $list) {
                            $formatName = $prefixName . '[' . trim((string)$name, '][') . "][$key]";
                            if (!is_array($list)) {
                                $this->apiBoxErrorCells[] = $name;
                                $this->apiBoxErrorMessages[$name] = sprintf($this->apiBoxErrorsList['error.wrong_value_array_format'], $formatName);
                                if ($returnFirst) {
                                    return false;
                                }
                            }
                            $this->check($list, $rulesValue, $formatName, $returnFirst, false);
                            if ($returnFirst && count($this->apiBoxErrorMessages)) {
                                return false;
                            }
                        }
                    } else {
                        $this->apiBoxErrorCells[] = $name;
                        $this->apiBoxErrorMessages[$name] = sprintf($this->apiBoxErrorsList['error.wrong_value_array_format'], $prefixName . $name);
                        if ($returnFirst) {
                            return false;
                        }
                    }
                    continue;
                }

                $rulesParts = explode('|', $rulesValue);
                $storageSize = [];
                $rulesList = [];
                foreach ($rulesParts as $position => $rule) {
                    $style = explode(':', $rule);
                    if (count($style) !== 2) {
                        if (count($style) === 1 && $position !== 0 && trim($style[0]) === 'required') {
                            throw new \ErrorException("The `required` parameter must be first in the field `" . $prefixName . "$name`");
                        }
                    } else {
                        $mark = $style[0];
                        $arg = $style[1];
                        if ($mark === '' || $mark === null || $arg === '') {
                            throw new \ErrorException("Unknown condition in field `" . $prefixName . "$name`");
                        }
                        if (in_array($mark, $rulesList)) {
                            throw new \ErrorException("The value `$mark` must be in the singular for the field `" . $prefixName . "$name`");
                        }
                        $rulesList[] = $mark;
                        $condList = explode(',', $arg);
                        // Проверка на совпадение с заданными типами значений
                        if ($mark === 'type') {
                            if ($position > 1) { // Тип должен быть в начале
                                throw new \ErrorException("The `type` parameter must be first or second (after `required`) in the field `" . $prefixName . "$name`");
                            }
                            $storageSize = $condList;
                            if (array_diff($condList, ['string', 'double', 'float', 'int', 'integer', 'regex', 'fullregex', 'bool', 'boolean', 'null', 'void', 'array'])) {
                                throw new \ErrorException("Unsupported value type in field `" . $prefixName . "$name`");
                            }
                            if (!in_array(gettype($inputValue), ['boolean', 'integer', 'double', 'float', 'string', 'array', 'NULL']) ||
                                (gettype($inputValue) === 'integer' && !in_array('int', $condList) && !in_array('integer', $condList)) ||
                                (is_array($inputValue) && !in_array('array', $condList)) ||
                                (is_null($inputValue) && !in_array('null', $condList, true)) ||
                                (is_string($inputValue)) && !(in_array('string', $condList) || ($inputValue === '' && in_array('void', $condList))) ||
                                (gettype($inputValue) === 'double' && !in_array('double', $condList) && !in_array('float', $condList)) ||
                                (gettype($inputValue) === 'boolean' && !in_array('bool', $condList) && !in_array('boolean', $condList))
                            ) {
                                $this->apiBoxErrorCells[] = $name;
                                $this->apiBoxErrorMessages[$name] = sprintf($this->apiBoxErrorsList['error.wrong_value_type'], gettype($inputValue), $prefixName . $name);
                                if ($returnFirst) {
                                    return false;
                                }
                            }
                        } else if ($mark === 'max' || $mark === 'min') {
                            // Проверка на совпадение с промежутком min и/или max
                            if (!array_diff($condList, ['int', 'integer', 'float', 'double']) && !is_float($arg) && !is_int($arg)) {
                                throw new \ErrorException("Wrong format of value `$mark` for field `" . $prefixName . "$name`. A numerical value was expected");
                            }
                            if ((is_float($inputValue) || is_int($inputValue)) && (($mark === 'max' && $arg < $inputValue) || ($mark === 'min' && $arg > $inputValue))) {
                                $this->apiBoxErrorCells[] = $name;
                                $this->apiBoxErrorMessages[$name] = sprintf($this->apiBoxErrorsList["error.wrong_value_{$mark}_format"], $prefixName . $name);
                                if ($returnFirst) {
                                    return false;
                                }
                            }
                        } else if ($mark === 'maxlength' || $mark === 'maxlen' || $mark === 'minlength' || $mark === 'minlen') {
                            if (!array_diff($condList, ['string']) && gettype($inputValue) !== 'string') {
                                throw new \ErrorException("Wrong format of value `$mark` for field `" . $prefixName . "$name`. A string value was expected");
                            }
                            if (!(in_array('void', $storageSize) && $inputValue === '') && gettype($inputValue) === 'string' && ((($mark === 'maxlength' || $mark === 'maxlen') && strlen($inputValue) > (int)$arg) || (($mark === 'minlength' || $mark === 'minlen') && strlen($inputValue) < (int)$arg))) {
                                $this->apiBoxErrorCells[] = $name;
                                $this->apiBoxErrorMessages[$name] = sprintf($this->apiBoxErrorsList['error.wrong_value_length'], $prefixName . $name);
                                if ($returnFirst) {
                                    return false;
                                }
                            }
                        } else if ($mark === 'enum') {
                            // Перечисления без учёта типа
                            if (!in_array($inputValue, $condList)) {
                                $this->apiBoxErrorCells[] = $name;
                                $this->apiBoxErrorMessages[$name] = sprintf($this->apiBoxErrorsList['error.wrong_value_enum'], $prefixName . $name);
                                if ($returnFirst) {
                                    return false;
                                }
                            }
                        } else if ($mark === 'regex' || $mark === 'fullregex') {
                            if (!array_diff($condList, ['string']) && gettype($inputValue) !== 'string') {
                                throw new \ErrorException("Wrong format of value `$mark` for field `" . $prefixName . "$name`. A string value was expected");
                            }
                            // Проверка по регулярному выражению
                            if (is_string($inputValue) && !preg_match($mark === 'regex' ? '|^' . $arg . '$|' : $arg, $inputValue)) {
                                $this->apiBoxErrorCells[] = $name;
                                $this->apiBoxErrorMessages[$name] = sprintf($this->apiBoxErrorsList['error.wrong_value_format'], $prefixName . $name);
                                if ($returnFirst) {
                                    return false;
                                }
                            }
                        } else {
                            throw new \ErrorException("Unknown condition `?:params` for field `" . $prefixName . "$name`");
                        }
                    }
                }
                // Неправильно назначенные величины
                if (!in_array("string", $storageSize) &&
                    (
                        in_array('maxlen', $rulesList) ||
                        in_array('minlen', $rulesList) ||
                        in_array('maxlength', $rulesList) ||
                        in_array('minlength', $rulesList)
                    )
                ) {
                    throw new \ErrorException("Specified range 'maxlength' or 'minlength' for a non-existent type 'string' for a field `" . $prefixName . "$name`");
                }
                if (!in_array("int", $storageSize) &&
                    !in_array("integer", $storageSize) &&
                    !in_array("float", $storageSize) &&
                    !in_array("double", $storageSize) &&
                    (in_array('max', $rulesList) || in_array('min', $rulesList))
                ) {
                    throw new \ErrorException("Specified span 'max' or 'min' for non-existent numeric type for field `" . $prefixName . "$name`");
                }
            }

        }
        return count($this->apiBoxErrorMessages) === 0;
    }

}

