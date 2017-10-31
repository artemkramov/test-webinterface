<?php

/**
 * Class CzechTest
 * Tests for czech web-interface
 */
class CzechTest extends CountryTest
{

    /**
     * Run queries
     */
    protected function runQueries()
    {
        $this->mdmInfo()
            ->fiscalizationLast()
            ->state()
            ->status()
            ->schema()
            ->deviceInfo()
            ->checkEET()
            ->checkCloud();
    }

    /**
     * Get device info
     * @return $this
     */
    public function deviceInfo()
    {
        $baseUriNet = '/cgi/tbl/Net';
        $baseUriInfo = '/cgi/dev_info';
        return $this->runQuery($baseUriNet, false)
            ->runQuery($baseUriInfo, true);
    }

    /**
     * Check for EET support
     * @return $this
     */
    public function checkEET()
    {
        $baseUriPrivate = '/cgi/vfycert/priv_key';
        $baseUriOwn = '/cgi/vfycert/own_cert';
        $baseUriEET = '/cgi/tbl/EET';
        $baseUriSSL = '/cgi/vfycert/ssl_server_cert';

        return $this->runQuery($baseUriPrivate, true)
            ->runQuery($baseUriOwn, true)
            ->runQuery($baseUriEET, true)
            ->runQuery($baseUriSSL, true);
    }

    /**
     * Check for cloud support
     * @return $this
     */
    public function checkCloud()
    {
        $baseUri = '/cgi/tbl/Cloud';
        return $this->runQuery($baseUri, false);
    }

}