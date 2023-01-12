<?php
/*
 * PagarmeCoreApiLib
 *
 * This file was automatically generated by APIMATIC v2.0 ( https://apimatic.io ).
 */

namespace PagarmeCoreApiLib\Models;

use JsonSerializable;

/**
 *3D-S payment authentication response
 */
class GetThreeDSecureResponse implements JsonSerializable
{
    /**
     * MPI Vendor
     * @required
     * @var string $mpi public property
     */
    public $mpi;

    /**
     * Electronic Commerce Indicator (ECI) (Opcional)
     * @required
     * @var string $eci public property
     */
    public $eci;

    /**
     * Online payment cryptogram, definido pelo 3-D Secure.
     * @required
     * @var string $cavv public property
     */
    public $cavv;

    /**
     * Identificador da transação (XID)
     * @required
     * @maps transaction_Id
     * @var string $transactionId public property
     */
    public $transactionId;

    /**
     * Url de redirecionamento de sucessso
     * @required
     * @maps success_url
     * @var string $successUrl public property
     */
    public $successUrl;

    /**
     * Constructor to set initial or default values of member properties
     * @param string $mpi           Initialization value for $this->mpi
     * @param string $eci           Initialization value for $this->eci
     * @param string $cavv          Initialization value for $this->cavv
     * @param string $transactionId Initialization value for $this->transactionId
     * @param string $successUrl    Initialization value for $this->successUrl
     */
    public function __construct()
    {
        if (5 == func_num_args()) {
            $this->mpi           = func_get_arg(0);
            $this->eci           = func_get_arg(1);
            $this->cavv          = func_get_arg(2);
            $this->transactionId = func_get_arg(3);
            $this->successUrl    = func_get_arg(4);
        }
    }


    /**
     * Encode this object to JSON
     */
    public function jsonSerialize()
    {
        $json = array();
        $json['mpi']            = $this->mpi;
        $json['eci']            = $this->eci;
        $json['cavv']           = $this->cavv;
        $json['transaction_Id'] = $this->transactionId;
        $json['success_url']    = $this->successUrl;

        return $json;
    }
}