<?php

/**
 * Class UkraineTest
 * Tests for ukrainian web-interface
 */
class UkraineTest extends CountryTest
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
            ->fiscalizationData();
    }

}