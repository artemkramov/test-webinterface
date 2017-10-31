<?php

/**
 * Class UkraineNilsenTest
 * Tests for Nielsen Ukrainian devices
 */
class UkraineNilsenTest extends CountryTest
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
            ->fiscalizationData()
            ->checkNilsonSupport();
    }

    /**
     * Check for Nielsen support
     * @return $this
     */
    public function checkNilsonSupport()
    {
        $baseUri = '/cgi/nls_signup?check';
        return $this->runQueryPost($baseUri, true);
    }

}