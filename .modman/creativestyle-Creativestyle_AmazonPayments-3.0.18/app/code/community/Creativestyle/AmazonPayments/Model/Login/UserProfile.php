<?php
/**
 * This file is part of the official Amazon Pay and Login with Amazon extension
 * for Magento 1.x
 *
 * (c) 2019 creativestyle GmbH. All Rights reserved
 *
 * Distribution of the derivatives reusing, transforming or being built upon
 * this software, is not allowed without explicit written permission granted
 * by creativestyle GmbH
 *
 * @category   Creativestyle
 * @package    Creativestyle_AmazonPayments
 * @copyright  2019 creativestyle GmbH
 * @author     Marek Zabrowarny <ticket@creativestyle.de>
 */

/**
 * Amazon user's profile model
 */
class Creativestyle_AmazonPayments_Model_Login_UserProfile
{
    const EMAIL_KEY = 'email';
    const NAME_KEY = 'name';
    const USER_ID_KEY = 'user_id';

    /**
     * User e-mail address
     *
     * @var string
     */
    private $_email;

    /**
     * User name
     *
     * @var string
     */
    private $_name;

    /**
     * User ID
     *
     * @var string
     */
    private $_userId;

    /**
     * User's profile constructor
     *
     * @param array $profileData
     */
    public function __construct($profileData = array())
    {
        $this->_email = isset($profileData[self::EMAIL_KEY]) ? $profileData[self::EMAIL_KEY] : null;
        $this->_name = isset($profileData[self::NAME_KEY]) ? $profileData[self::NAME_KEY] : null;
        $this->_userId = isset($profileData[self::USER_ID_KEY]) ? $profileData[self::USER_ID_KEY] : null;
    }

    /**
     * @return string
     */
    public function getEmail()
    {
        return $this->_email;
    }

    /**
     * @param string $email
     */
    public function setEmail($email)
    {
        $this->_email = $email;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->_name;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->_name = $name;
    }

    /**
     * @return string
     */
    public function getUserId()
    {
        return $this->_userId;
    }

    /**
     * @param string $userId
     */
    public function setUserId($userId)
    {
        $this->_userId = $userId;
    }

    /**
     * @return bool
     */
    public function validate()
    {
        return $this->getEmail() && $this->getName() && $this->getUserId();
    }
}
