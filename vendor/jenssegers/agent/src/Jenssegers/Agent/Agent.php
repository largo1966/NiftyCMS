<?php namespace Jenssegers\Agent;

use BadMethodCallException;
use Mobile_Detect;

class Agent extends Mobile_Detect {

    /**
     * List of additional operating systems.
     *
     * @var array
     */
    protected static $additionalOperatingSystems = array(
        'Windows'           => 'Windows',
        'OS X'              => 'OS X',
        'Debian'            => 'Debian',
        'Ubuntu'            => 'Ubuntu',
        'Macintosh'         => 'PPC',
        'OpenBSD'           => 'OpenBSD',
        'Linux'             => 'Linux',
    );


    /**
     * List of additional browsers.
     *
     * @var array
     */
    protected static $additionalBrowsers = array(
        'Chrome'            => 'Chrome',
        'Firefox'           => 'Firefox',
        'Safari'            => 'Safari',
        'Opera'             => 'Opera',
        'IE'                => 'MSIE|IEMobile|MSIEMobile',
        'Netscape'          => 'Netscape',
        'Mozilla'           => 'Mozilla',
    );


    /**
     * List of robots.
     *
     * @var array
     */
    protected static $robots = array(
        'Googlebot'         => 'googlebot',
        'MSNBot'            => 'msnbot',
        'Baiduspider'       => 'baiduspider',
        'Bing'              => 'bingbot',
        'Yahoo'             => 'yahoo',
        'Lycos'             => 'lycos',
    );


    /**
     * Get all detection rules. These rules include the additional
     * platforms and browsers.
     *
     * @return array
     */
    public function getDetectionRulesExtended()
    {
        static $rules;

        if (!$rules)
        {
            $rules = array_merge(
                static::$phoneDevices,
                static::$tabletDevices,
                static::$operatingSystems,
                static::$additionalOperatingSystems, // NEW
                static::$browsers,
                static::$additionalBrowsers, // NEW
                static::$utilities
            );
        }

        return $rules;
    }


    /**
     * Retrieve the current set of rules.
     *
     * @return array
     */
    public function getRules()
    {
        if ($this->detectionType == static::DETECTION_TYPE_EXTENDED)
        {
            return static::getDetectionRulesExtended();
        }
        else
        {
            return static::getMobileDetectionRules();
        }
    }


    /**
     * Get accept languages.
     *
     * @return array
     */
    public function languages($acceptLanguage = null)
    {
        if (!$acceptLanguage)
        {
            $acceptLanguage = $this->getHttpHeader('HTTP_ACCEPT_LANGUAGE');
        }

        if ($acceptLanguage)
        {
            return explode(',', preg_replace('/(;q=[0-9\.]+)/i', '', strtolower(trim($acceptLanguage))));
        }

        return array();
    }


    /**
     * Match a detection rule and return the matched key.
     *
     * @param  array     $rules
     * @param  null      $userAgent
     * @return string
     */
    protected function findDetectionRulesAgainstUA(array $rules, $userAgent = null)
    {
        // Loop given rules
        foreach ($rules as $key => $regex)
        {
            if (empty($regex)) continue;

            // Check match
            if ($this->match($regex, $userAgent)) return $key;
        }

        return false;
    }


    /**
     * Get the browser name.
     *
     * @return string
     */
    public function browser($userAgent = null)
    {
        // Get browser rules
        $rules = array_merge(
            static::$browsers,
            static::$additionalBrowsers // NEW
        );

        return $this->findDetectionRulesAgainstUA($rules, $userAgent);
    }


    /**
     * Get the platform name.
     *
     * @param  string $userAgent
     * @return string
     */
    public function platform($userAgent = null)
    {
        // Get platform rules
        $rules = array_merge(
            static::$operatingSystems,
            static::$additionalOperatingSystems // NEW
        );

        return $this->findDetectionRulesAgainstUA($rules, $userAgent);
    }


    /**
     * Get the device name.
     *
     * @param  string $userAgent
     * @return string
     */
    public function device($userAgent = null)
    {
        // Get device rules
        $rules = array_merge(
            static::$phoneDevices,
            static::$tabletDevices,
            static::$utilities
        );

        return $this->findDetectionRulesAgainstUA($rules, $userAgent);
    }


    /**
     * Check if device is a robot.
     *
     * @param  string  $userAgent
     * @return boolean
     */
    public function isRobot($userAgent = null)
    {
        // Get bot rules
        $rules = array_merge(
            array(static::$utilities['Bot']),
            static::$robots // NEW
        );

        foreach ($rules as $regex)
        {
            // Check for match
            if ($this->match($regex, $userAgent)) return true;
        }

        return false;
    }


    /**
     * Changing detection type to extended.
     *
     * @inherit
     */
    public function __call($name, $arguments)
    {
        //make sure the name starts with 'is', otherwise
        if (substr($name, 0, 2) != 'is')
        {
            throw new BadMethodCallException("No such method exists: $name");
        }

        $this->setDetectionType(self::DETECTION_TYPE_EXTENDED);

        $key = substr($name, 2);

        return $this->matchUAAgainstKey($key);
    }

}
