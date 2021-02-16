<?php

class Token extends BaseModel
{
    const STATE_ACTIVE = 1;
    const STATE_REVOKED = 0;

    public $account_id;
    public $token;
    public $refresh;
    public $refresh_exp;

	public function getSource()
    {
        return 'token';
    }

    /**
     * Allows to query a set of records that match the specified conditions
     *
     * @param mixed $parameters
     * @return JwtData[]|JwtData|\Phalcon\Mvc\Model\ResultSetInterface
     */
    public static function find($parameters = null)
    {
        return parent::find($parameters);
    }

    /**
     * Allows to query the first record that match the specified conditions
     *
     * @param mixed $parameters
     * @return JwtData|\Phalcon\Mvc\Model\ResultInterface
     */
    public static function findFirst($parameters = null)
    {
        return parent::findFirst($parameters);
    }
}