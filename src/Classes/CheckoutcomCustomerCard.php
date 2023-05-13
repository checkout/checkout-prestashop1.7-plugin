<?php

namespace CheckoutCom\PrestaShop\Classes;

use PrestaShop\PrestaShop\Adapter\Entity\Db;

class CheckoutcomCustomerCard
{
    /**
     * Save card in db
     * @param $response
     * @param $customerId
     * @return bool
     * @throws \PrestaShopDatabaseException
     */
    public static function saveCard($response, $customerId)
    {
        $source   = $response['source'];
        $sourceId = $source['id'];
        $last4    = $source['last4'];
        $scheme   = $source['scheme'];
        $isMada   = null;

        if(static::sourceExist($customerId, $sourceId)) {
            return false;
        }

        $context = \Context::getContext();

        if($context->cookie->__isset('is_mada') ){
            $isMada = $context->cookie->__isset('is_mada');
            $context->cookie->__unset('is_mada');
        }

        $db = Db::getInstance();
        $db->insert('cko_cards', array(
            'customer_id' => $customerId,
            'source_id'   => $sourceId,
            'last_four'   => $last4,
            'card_scheme' => $scheme,
            'is_mada'     => $isMada

        ),false,true, Db::REPLACE);

        return true;
    }

    /**
     * Check if source id exist in db
     * @param $customerId
     * @param $sourceId
     * @return bool
     * @throws \PrestaShopDatabaseException
     */
    protected static function sourceExist($customerId, $sourceId)
    {
        $sql = 'SELECT * FROM '._DB_PREFIX_."cko_cards WHERE `customer_id` = '{$customerId}' AND `source_id` = '{$sourceId}'";
        $row = Db::getInstance()->executeS($sql);

        if($row){
            return true;
        }

        return false;
    }

    /**
     * Returned saved card for specific customer
     * @param $customerId
     * @return array|false|\mysqli_result|null|\PDOStatement|resource
     * @throws \PrestaShopDatabaseException
     */
    public static function getCardList($customerId)
    {
        $sql = 'SELECT * FROM '._DB_PREFIX_."cko_cards WHERE `customer_id` = '{$customerId}' ";
        $row = Db::getInstance()->executeS($sql);

        return $row;
    }

    /**
     * Retrieve souce id for specific customer
     * @param $entityId
     * @param $customerId
     * @return array|bool|null|object
     */
    public static function getSourceId($entityId, $customerId)
    {
        $sql = 'SELECT `source_id` FROM '._DB_PREFIX_."cko_cards WHERE `entity_id` = '{$entityId}' AND `customer_id` = '{$customerId}'";
        $row = Db::getInstance()->getRow($sql);

        return $row['source_id'];
    }

    public static function removeCard($customerId, array $entityId)
    {
        $result = false;
        foreach ($entityId as $key) {
            $db = Db::getInstance();
            $sql = 'Delete from ' . _DB_PREFIX_ ."cko_cards where `customer_id`='{$customerId}' AND entity_id ='{$key}'";

            if (!$db->execute($sql)) {
                return $result;
            }
//                print_r('Error has occured when deleting card ');
        }

        return true;
    }
}