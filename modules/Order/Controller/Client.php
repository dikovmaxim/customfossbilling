<?php

/**
 * Copyright 2022-2025 FOSSBilling
 * Copyright 2011-2021 BoxBilling, Inc.
 * SPDX-License-Identifier: Apache-2.0.
 *
 * @copyright FOSSBilling (https://www.fossbilling.org)
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache-2.0
 */

namespace Box\Mod\Order\Controller;

use FOSSBilling\Routing\RouteGroup;

class Client implements \FOSSBilling\InjectionAwareInterface
{
    protected ?\Pimple\Container $di = null;

    public function setDi(\Pimple\Container $di): void
    {
        $this->di = $di;
    }

    public function getDi(): ?\Pimple\Container
    {
        return $this->di;
    }

    public function register(\Box_App &$app)
    {
        $app->get('/order', 'get_products', [], static::class);
        $app->get('/order/:id', 'get_configure_product', ['id' => '[0-9]+'], static::class);
        $app->get('/order/:slug', 'get_configure_product_by_slug', ['slug' => '[a-z0-9-]+'], static::class);

        $dashboard = RouteGroup::dashboard($app);
        $dashboard->get('/services', 'get_orders', [], static::class);
        $dashboard->get('/services/manage/:id', 'get_order', ['id' => '[0-9]+'], static::class);

        $app->get('/order/service', 'legacy_redirect_to_dashboard_services', [], static::class);
        $app->get('/order/service/manage/:id', 'legacy_redirect_to_dashboard_service', ['id' => '[0-9]+'], static::class);
    }

    public function get_products(\Box_App $app)
    {
        return $app->render('mod_order_index');
    }

    public function get_configure_product_by_slug(\Box_App $app, $slug)
    {
        $api = $this->di['api_guest'];
        $product = $api->product_get(['slug' => $slug]);
        $tpl = 'mod_service' . $product['type'] . '_order';
        if ($api->system_template_exists(['file' => $tpl . '.html.twig'])) {
            return $app->render($tpl, ['product' => $product]);
        }

        return $app->render('mod_order_product', ['product' => $product]);
    }

    public function get_configure_product(\Box_App $app, $id)
    {
        $api = $this->di['api_guest'];
        $product = $api->product_get(['id' => $id]);
        $tpl = 'mod_service' . $product['type'] . '_order';
        if ($api->system_template_exists(['file' => $tpl . '.html.twig'])) {
            return $app->render($tpl, ['product' => $product]);
        }

        return $app->render('mod_order_product', ['product' => $product]);
    }

    public function get_orders(\Box_App $app)
    {
        $this->di['is_client_logged'];

        RouteGroup::ensureDashboardPath($app, 'services', $this->di['request']->getPathInfo());

        return $app->render('mod_order_list');
    }

    public function get_order(\Box_App $app, $id)
    {
        RouteGroup::ensureDashboardPath($app, 'services/manage/' . $id, $this->di['request']->getPathInfo());

        $api = $this->di['api_client'];
        $data = [
            'id' => $id,
        ];
        $order = $api->order_get($data);

        return $app->render('mod_order_manage', ['order' => $order]);
    }

    public function legacy_redirect_to_dashboard_services(\Box_App $app): never
    {
        $app->redirect(RouteGroup::path('services'));
    }

    public function legacy_redirect_to_dashboard_service(\Box_App $app, $id): never
    {
        $app->redirect(RouteGroup::path('services/manage/' . $id));
    }
}
