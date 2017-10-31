<?php

/**
 * Class CountryTest
 * Class which defines basic queries for all types
 * of web-interface
 */
class CountryTest
{

    const HTTP_METHOD_GET = 'GET';

    const HTTP_METHOD_POST = 'POST';

    /**
     * History of test process
     * @var array
     */
    private $history = [];

    /**
     * Codeigniter entity
     * @var mixed
     */
    private $CI;

    /**
     * CountryTest constructor.
     */
    public function __construct()
    {
    }

    /**
     * @param $CI
     * @return $this
     */
    public function setCI($CI)
    {
        $this->CI = $CI;
        return $this;
    }

    /**
     * @return $this
     */
    public function setClient()
    {
        $this->CI->load->library('HttpClient', $this->CI->session->userdata('credentials'));
        return $this;
    }

    /**
     * Run all queries
     */
    public function run()
    {
        $this->history = [];
        $this->runQueries();
    }

    /**
     * Method which is needed to be redefined in children classes
     */
    protected function runQueries()
    {
    }

    /**
     * Load modem info and data about network interfaces
     * @return $this
     */
    public function mdmInfo()
    {
        $baseUri = '/cgi/mdm_info';
        $baseUriNetifs = '/cgi/netifs';
        return $this->runQuery($baseUri, false)
            ->runQuery($baseUriNetifs, true);
    }

    /**
     * Load schema data and translations
     * @return $this
     */
    public function schema()
    {
        $baseUri = '/cgi/tbl';
        $baseUriDesc = '/desc';
        $baseUriDescExt = '/desc-ext';
        return $this->runQuery($baseUri, true)
            ->runQuery($baseUriDesc, true)
            ->runQuery($baseUriDescExt, true);
    }

    /**
     * Load current state of device
     * @return CountryTest
     */
    public function state()
    {
        $baseUri = '/cgi/state';
        return $this->runQuery($baseUri, true);
    }

    /**
     * Load modem status
     * @return CountryTest
     */
    public function status()
    {
        $baseUri = '/cgi/status';
        return $this->runQuery($baseUri, false);
    }

    /**
     * Load last fiscalization data
     * @return CountryTest
     */
    public function fiscalizationLast()
    {
        $baseUri = '/cgi/tbl/FDay?s=-1';
        return $this->runQuery($baseUri, true);
    }

    /**
     * Load fiscalization info
     * @return $this
     */
    public function fiscalizationData()
    {
        $baseUriTax = '/cgi/tbl/FTax';
        $baseUriSbr = '/cgi/tbl/FSbr';
        return $this->runQuery($baseUriTax, false)
            ->runQuery($baseUriSbr, false);
    }

    /**
     * Run GET query to device
     * @param string $baseUri
     * @param bool $isNecessary
     * @return $this
     */
    protected function runQuery($baseUri, $isNecessary = false)
    {
        return $this->query($baseUri, self::HTTP_METHOD_GET, $isNecessary);
    }

    /**
     * Run query to device and parse data
     * @param $baseUri
     * @param $method
     * @param $isNecessary
     * @param array $data
     * @param bool $isJSON
     * @return $this
     */
    private function query($baseUri, $method, $isNecessary, $data = [], $isJSON = true)
    {
        $response = $this->CI->httpclient->request($baseUri, $method, [], $data);
        $test = new ResponseTest();
        $test->uri = $baseUri;
        $test->result = ResponseTest::STATUS_SUCCESS;
        if (!empty($response['errorMessage'])) {
            $test->result = ResponseTest::STATUS_FAILED;
        }
        else {
            if (!isset($response['data']) && $isJSON) {
                $test->result = ResponseTest::STATUS_PARSE_ERROR;
            }
        }
        $test->isNecessary = $isNecessary;
        $this->history[] = $test;
        return $this;
    }

    /**
     * Run POST query to device
     * @param string $baseUri
     * @param bool $isNecessary
     * @return $this
     */
    protected function runQueryPost($baseUri, $isNecessary = false)
    {
        return $this->query($baseUri, self::HTTP_METHOD_POST, $isNecessary);
    }

    /**
     * Get history about the test
     * @return array
     */
    public function getHistory()
    {
        return $this->history;
    }

}

/**
 * Class ResponseTest
 * Describes the result of test
 */
class ResponseTest
{

    const STATUS_SUCCESS = "success";

    const STATUS_FAILED = "failed";

    const STATUS_PARSE_ERROR = "parse error";

    /**
     * @var string
     */
    public $uri;

    /**
     * @var string
     */
    public $result;

    /**
     * @var bool
     */
    public $isNecessary = false;

}