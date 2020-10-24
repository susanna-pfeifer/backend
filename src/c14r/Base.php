<?php

namespace C14r\Directus;

use Directus\Database\TableGatewayFactory;
use Directus\Application\Application;
use Directus\Collection\Collection;
use Directus\Services\ItemsService;
use Directus\Hook\Payload;
use Psr\Container\ContainerInterface;

class Base
{
    public static function getApplication(): Application
    {
        return Application::getInstance();
    }

    public static function getContainer(): ContainerInterface
    {
        return static::getApplication()->getContainer();
    }

    public static function getItemsService(): ItemsService
    {
        return new ItemsService(static::getContainer());
    }

    public static function getDB()
    {
        return static::getContainer()->get('database');
    }

    public static function getACL()
    {
        return static::getContainer()->get('acl');
    }

    public static function getTableGateway(string $collection, $acl = null)
    {
        return TableGatewayFactory::create($collection, [
            'connection' => static::getDB(),
            'acl' => $acl
        ]);
    }

    public static function findByIds($collection, $ids, array $params = [], $acl = true): Collection
    {
        $is = static::getItemsService();
        $items = $is->findByIds($collection, $ids, $params, $acl);

        return new Collection($items['data']);
    }

    public static function mergePayloadAndOld(Payload $payload, Collection $old): Collection
    {
        return new Collection(array_replace_recursive($old->toArray(), $payload->toArray()));
    }
}
