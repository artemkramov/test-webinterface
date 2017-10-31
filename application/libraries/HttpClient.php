<?php
defined('BASEPATH') OR exit('No direct script access allowed');


/**
 * Class HttpClient
 * HTTP client for making requests to device
 */
class HttpClient
{

    const HTTP_METHOD_GET = 'GET';

    const HTTP_METHOD_POST = 'POST';

    /**
     * Authentication method which is used in device
     */
    const AUTH_METHOD = 'digest';

    /**
     * @var \GuzzleHttp\Client
     */
    protected $client;

    /**
     * @var string
     */
    private $ip;

    /**
     * @var string
     */
    private $login;

    /**
     * @var string
     */
    private $password;

    /**
     * @var string
     */
    private $type;

    /**
     * Contains data about the structure of device's tables
     * @var array
     */
    private $schema = [];

    /**
     * Contains data of each table
     * @var array
     */
    private $data = [];

    /**
     * Describes all errors
     * @var array
     */
    private $schemaErrors = [];

    /**
     * HttpClient constructor.
     * @param $data
     */
    public function __construct($data)
    {
        $this->setIp($data['ipAddress'])
            ->setLogin($data['login'])
            ->setPassword($data['password'])
            ->setType($data['type']);
        $this->client = new GuzzleHttp\Client([
            'base_uri' => $this->ip
        ]);
    }

    /**
     * @param $ip
     * @return $this
     */
    public function setIp($ip)
    {
        $this->ip = $ip;
        return $this;
    }

    /**
     * @param $login
     * @return $this
     */
    public function setLogin($login)
    {
        $this->login = $login;
        return $this;
    }

    /**
     * @param $password
     * @return $this
     */
    public function setPassword($password)
    {
        $this->password = $password;
        return $this;
    }

    /**
     * @param $type
     * @return $this
     */
    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }

    /**
     * @return array
     */
    public function connect()
    {
        /**
         * Trying to connect to device
         */
        $tableData = $this->request('/cgi/state', self::HTTP_METHOD_GET);
        if (empty($tableData['errorMessage'])) {
            $data = [
                'credentials' => [
                    'ipAddress' => $this->ip,
                    'login'     => $this->login,
                    'password'  => $this->password,
                    'type'      => $this->type
                ]
            ];
            $CI =& get_instance();
            $CI->session->set_userdata($data);
        }
        return $tableData;
    }

    /**
     * Run all tests due to the current
     * web-interface type
     * @return array
     */
    private function runTests()
    {
        /**
         * Include all tests
         */
        $modelPath = dirname(dirname(__FILE__)) . '/tests';
        $files = glob($modelPath . '/*.php');
        require_once $modelPath . '/CountryTest.php';
        foreach ($files as $file) {
            require_once($file);
        }
        $CI =& get_instance();
        /**
         * Choose the appropriate test due to the user choice
         * @var CountryTest $test
         */
        switch ($this->type) {
            case 'ukraine':
                $test = new UkraineTest();
                break;
            case 'czech':
                $test = new CzechTest();
                break;
            case 'ukraine-nilson':
                $test = new UkraineNilsenTest();
                break;
            case 'belarus':
                $test = new BelarusTest();
                break;
            default:
                $test = new UkraineTest();
                break;
        }
        /**
         * Set data and run test
         */
        $test->setCI($CI)
            ->setClient();
        $test->run();
        return $test->getHistory();
    }

    /**
     * Build full report about device
     * @return array
     */
    public function buildData()
    {
        /**
         * Run tests for specific web-interface
         */
        $history = $this->runTests();

        /**
         * Fetch schema data
         */
        $baseUrl = '/cgi/tbl';
        $tableData = $this->request($baseUrl, self::HTTP_METHOD_GET);
        if (empty($tableData['errorMessage'])) {
            $this->schema = $tableData['data'];
            if (isset($this->schema) && is_array($this->schema)) {
                foreach ($this->schema as $schema) {
                    /**
                     * Initialize error container
                     */
                    $schemaID = $schema->id;
                    if (!empty($schemaID)) {
                        $this->schemaErrors[$schemaID] = [
                            'errors'      => [],
                            'warnings'    => [],
                            'wrongFields' => [
                                'schema' => [],
                                'data'   => []
                            ]
                        ];
                        /**
                         * Fetch data for current table
                         */
                        $uri = $baseUrl . '/' . $schemaID;
                        $data = $this->request($uri, self::HTTP_METHOD_GET);
                        if (empty($data['errorMessage'])) {
                            $this->data[$schemaID] = [];
                            $values = $data['data'];
                            /**
                             * Check if the schema type corresponds to result data
                             */
                            if (!((is_array($values) && isset($schema->tbl)) || (!is_array($values) && !isset($schema->tbl)))) {
                                $this->schemaErrors[$schemaID]['warnings'][] = 'Type mismatch between schema and data (check tbl flag)';
                            }
                            /**
                             * Check if the schema type is table
                             */
                            if (isset($schema->tbl) && $schema->tbl) {
                                $key = isset($schema->key) ? $schema->key : 'id';
                                /**
                                 * Check if the primary key presents in the table
                                 */
                                $isKeyFound = false;
                                foreach ($schema->elems as $field) {
                                    if ($key == $field->name) {
                                        $isKeyFound = true;
                                        break;
                                    }
                                }
                                if (!$isKeyFound) {
                                    $this->schemaErrors[$schemaID]['errors'][] = 'No key is set for table. Please, set "key" property for schema';
                                }
                            }
                            /**
                             * Write data due to its type (array or object)
                             */
                            if (is_object($values)) {
                                $this->data[$schemaID] = array_keys(get_object_vars($values));
                            }
                            if (is_array($values) && !empty($values)) {
                                $this->data[$schemaID] = array_keys(get_object_vars(reset($values)));
                            }
                            /**
                             * If data is empty
                             * than check if the data format is correct
                             * In other case append the error
                             */
                            if (empty($this->data[$schemaID])) {
                                if (isset($values)) {
                                    $this->schemaErrors[$schemaID]['warnings'][] = 'Empty response from ' . $uri;
                                } else {
                                    $this->schemaErrors[$schemaID]['errors'][] = 'Cannot parse JSON from ' . $uri;
                                }
                            }
                        } else {
                            /**
                             * Write that we can't access table data for this URI
                             */
                            $this->schemaErrors[$schemaID]['errors'][] = 'Table data from ' . $uri . ' not found';
                        }
                    }
                }
            }
        }

        /**
         * Summary data about errors and warnings
         */
        $aggregate = [
            'errors'   => 0,
            'warnings' => 0
        ];

        /**
         * Search mismatches between schema of tables and its data
         */
        $this->searchFieldMismatch();

        /**
         * Summarize errors and warnings
         */
        foreach ($this->schemaErrors as $key => $data) {
            $aggregate['errors'] += count($data['errors']);
            $aggregate['warnings'] += count($data['warnings']);
            foreach ($data['wrongFields'] as $wrongFields) {
                foreach ($wrongFields as $fields) {
                    $aggregate['warnings'] += count($fields);
                }
            }
        }
        return [
            'schema'       => $this->schema,
            'data'         => $this->data,
            'schemaErrors' => $this->schemaErrors,
            'aggregate'    => $aggregate,
            'history'      => $history
        ];
    }

    /**
     * Search all mismatches between
     * the schema and data
     */
    private function searchFieldMismatch()
    {
        if (isset($this->schema)) {
            /**
             * Loop through all tables
             */
            foreach ($this->schema as $schema) {
                $fields = $schema->elems;
                $dataFields = array_key_exists($schema->id, $this->data) ? $this->data[$schema->id] : [];
                /**
                 * Analyze which schema fields are absent in data fields
                 */
                foreach ($fields as $field) {
                    if (isset($field->name)) {
                        if (!in_array($field->name, $dataFields)) {
                            $this->schemaErrors[$schema->id]['wrongFields']['schema'][] = $field->name;
                        }
                    }
                }
                /**
                 * Analyze which data fields are absent in schema fields
                 */
                foreach ($dataFields as $dataField) {
                    $isAbsent = true;
                    foreach ($fields as $field) {
                        if (!empty($dataField) && $dataField == $field->name) {
                            $isAbsent = false;
                        }
                    }
                    if ($isAbsent) {
                        $this->schemaErrors[$schema->id]['wrongFields']['data'][] = $dataField;
                    }
                }
            }
        }
    }

    /**
     * Make request to the given endpoint
     * @param string $uri - endpoint
     * @param string $method - HTTP method
     * @param array $params - GET parameters
     * @param array $data - POST data
     * @return array
     */
    public function request($uri, $method, $params = [], $data = [])
    {
        /**
         * Set response type
         */
        $response = [
            'statusCode'   => 500,
            'data'         => [],
            'errorMessage' => ''
        ];
        /**
         * Set GET parameters
         */
        if (!empty($params)) {
            $uri = $uri . '?' . http_build_query($params);
        }
        try {
            /**
             * Run query
             */
            $queryResponse = $this->client->request($method, $uri, [
                'auth'            => [
                    $this->login, $this->password, self::AUTH_METHOD
                ],
                'verify'          => false,
                'connect_timeout' => 6
            ]);
            $response['statusCode'] = $queryResponse->getStatusCode();
            /**
             * Remove UTF-8 BOM symbol from the beginning of the string
             */
            $response['data'] = json_decode($this->removeUtf8Bom($queryResponse->getBody()->getContents()));
            $response['errorMessage'] = $this->decodeError($response['statusCode']);
        } catch (GuzzleHttp\Exception\ClientException $ex) {
            $response['errorMessage'] = $this->decodeError($ex->getCode());
        } catch (Exception $ex) {
            $response['errorMessage'] = $this->decodeError(500);
        }
        return $response;
    }

    /**
     * Remove BOM characters from the text
     * @param string $text
     * @return string
     */
    private function removeUtf8Bom($text)
    {
        $bom = pack('H*', 'EFBBBF');
        $text = preg_replace("/^$bom/", '', $text);
        return $text;
    }

    /**
     * Decode status code of response
     * and return its appropriate description
     * @param integer $statusCode
     * @return string
     */
    private function decodeError($statusCode)
    {
        $response = '';
        switch ($statusCode) {
            case 200:
                break;
            case 401:
                $response = 'Unauthorized error';
                break;
            case 404:
                $response = 'Not found error';
                break;
            default:
                $response = 'Unavailable destination';
                break;
        }
        return $response;
    }


}