<?php

/**
 * Class BelarusTest
 * Test for Belraus web-interface
 */
class BelarusTest extends CountryTest
{

    /**
     * Run all queries
     */
    protected function runQueries()
    {
        $this->mdmInfo()
            ->fiscalizationLast()
            ->state()
            ->status()
            ->schema()
            ->deviceInfo();
    }

    /**
     * Get device info
     * @return $this
     */
    public function deviceInfo()
    {
        $baseUriInfo = '/cgi/dev_info';
        return $this->runQuery($baseUriInfo, true);
    }

}