<?php
namespace Codeception\Module\Yandex\StructuredData;

/*
 * This file is part of the Codeception Yandex Module project
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read
 * LICENSE file that was distributed with this source code.
 *
 */

/**
 * Class ValidationResponse
 * @package Codeception\Module\Yandex\StructuredData
 */
class ValidationResponse
{
    const MICROFORMAT = 'microformat';
    const RDFA = 'rdfa';
    const MICRODATA = 'microdata';
    const JSONLD = 'json-ld';

    /**
     * @var string
     */
    protected $id;

    /**
     * @var mixed
     */
    protected $data;

    /**
     * ValidationResponse constructor.
     * @param string $id
     * @param array $data
     */
    public function __construct($id, array $data)
    {
        $this->id = $id;
        $this->data = $data;
    }

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @param string $id
     */
    public function setId(string $id)
    {
        $this->id = $id;
    }

    /**
     * @return array
     */
    public function getData(): array
    {
        return $this->data;
    }

    /**
     * @return array
     */
    public function getDataWithReplacedKeys(): array
    {
        $data = $this->replaceSpecialCharsInKeys($this->getData());
        return $data;
    }

    /**
     * @param array $input
     * @return array
     */
    protected function replaceSpecialCharsInKeys(array $input)
    {
        $return = [];
        foreach ($input as $key => $value) {
            $escapers = ['\\', '/', '\'', '\n', '\r', '\t', '\x08', '\x0c', '.', '-', ':', '@'];
            $replacements = ['__', '_', '_', '\\n', '\\r', '\\t', '\\f', '\\b', '_', '_', '_', '_'];
            $key = str_replace($escapers, $replacements, $key);

            if (is_array($value)) {
                $value = $this->replaceSpecialCharsInKeys($value);
            }

            $return[$key] = $value;
        }
        return $return;
    }

    /**
     * @param array $data
     */
    public function setData(array $data)
    {
        $this->data = $data;
    }

    /**
     * @param string $format
     * @return bool
     */
    public function isValid($format = '')
    {
        $result = true;
        $data = $this->getData();

        if ($format === '') {
            foreach ($data as $formatData) {
                if (is_array($formatData)) {
                    foreach ($formatData as $singleFormatData) {
                        if (isset($singleFormatData['#error'])) {
                            $result = false;
                            break;
                        }
                    }
                }
            }
        } else {
            if (isset($data[$format])) {
                $formatData = $data[$format];
                foreach ($formatData as $singleFormatData) {
                    if (isset($singleFormatData['#error'])) {
                        $result = false;
                        break;
                    }
                }
            }
        }
        return $result;
    }

    /**
     * @param string $format
     * @return array
     */
    public function getErrors($format = '')
    {
        $result = [];
        $data = $this->getData();
        if ($format === '') {
            foreach ($data as $formatData) {
                if (is_array($formatData)) {
                    foreach ($formatData as $singleFormatData) {
                        if (isset($singleFormatData['#error'])) {
                            if (is_array($singleFormatData['#error'])) {
                                foreach ($singleFormatData['#error'] as $error) {
                                    $result[] = $error;
                                }
                            } else {
                                $result[] = $singleFormatData['#error'];
                            }
                        }
                    }
                }
            }
        } else {
            if (isset($data[$format])) {
                $formatData = $data[$format];
                foreach ($formatData as $singleFormatData) {
                    if (isset($singleFormatData['#error'])) {
                        if (is_array($singleFormatData['#error'])) {
                            foreach ($singleFormatData['#error'] as $error) {
                                $result[] = $error;
                            }
                        } else {
                            $result[] = $singleFormatData['#error'];
                        }
                    }
                }
            }
        }
        return $result;
    }

    /**
     * @param string $format
     * @return array
     */
    public function getErrorsFormatted($format = '')
    {
        $result = [];
        $errors = $this->getErrors($format);
        foreach ($errors as $error) {
            $result[] = implode(';', $error);
        }
        return $result;
    }
}
