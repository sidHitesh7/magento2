<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Url;

use Magento\Framework\Url\RouteParamsResolverInterface;

/**
 * Route params resolver.
 *
 * @method $this setType(string $type)
 * @method string getType()
 * @method $this setScope(string $scope)
 * @method string getScope()
 * @method $this setSecureIsForced(bool $isForced)
 * @method bool getSecureIsForced()
 * @method $this setSecure(bool $isForced)
 * @method bool getSecure()
 */
class RouteParamsResolver extends \Magento\Framework\Object implements RouteParamsResolverInterface
{
    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $request;

    /**
     * @var \Magento\Framework\Url\QueryParamsResolverInterface
     */
    protected $queryParamsResolver;

    /**
     * @param \Magento\Framework\App\RequestInterface $request
     * @param \Magento\Framework\Url\QueryParamsResolverInterface $queryParamsResolver
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Framework\Url\QueryParamsResolverInterface $queryParamsResolver,
        array $data = []
    ) {
        parent::__construct($data);
        $this->request = $request;
        $this->queryParamsResolver = $queryParamsResolver;
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function setRouteParams(array $data, $unsetOldParams = true)
    {
        if (isset($data['_type'])) {
            $this->setType($data['_type']);
            unset($data['_type']);
        }

        if (isset($data['_forced_secure'])) {
            $this->setSecure((bool)$data['_forced_secure']);
            $this->setSecureIsForced(true);
            unset($data['_forced_secure']);
        } elseif (isset($data['_secure'])) {
            $this->setSecure((bool)$data['_secure']);
            unset($data['_secure']);
        }

        if (isset($data['_absolute'])) {
            unset($data['_absolute']);
        }

        if ($unsetOldParams) {
            $this->unsetData('route_params');
        }

        if (isset($data['_current'])) {
            if (is_array($data['_current'])) {
                foreach ($data['_current'] as $key) {
                    if (array_key_exists($key, $data) || !$this->request->getUserParam($key)) {
                        continue;
                    }
                    $data[$key] = $this->request->getUserParam($key);
                }
            } elseif ($data['_current']) {
                foreach ($this->request->getUserParams() as $key => $value) {
                    if (array_key_exists($key, $data) || $this->getRouteParam($key)) {
                        continue;
                    }
                    $data[$key] = $value;
                }
                foreach ($this->request->getQuery() as $key => $value) {
                    $this->queryParamsResolver->setQueryParam($key, $value);
                }
            }
            unset($data['_current']);
        }

        if (isset($data['_use_rewrite'])) {
            unset($data['_use_rewrite']);
        }

        foreach ($data as $key => $value) {
            $this->setRouteParam($key, $value);
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setRouteParam($key, $data)
    {
        $params = $this->_getData('route_params');
        if (isset($params[$key]) && $params[$key] == $data) {
            return $this;
        }
        $params[$key] = $data;
        $this->unsetData('route_path');
        return $this->setData('route_params', $params);
    }

    /**
     * {@inheritdoc}
     */
    public function getRouteParams()
    {
        return $this->_getData('route_params');
    }

    /**
     * {@inheritdoc}
     */
    public function getRouteParam($key)
    {
        return $this->getData('route_params', $key);
    }
}
