<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\NewRelicReporting\Model\Observer;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\PersonName\DataObjectFormatter;
use Magento\Framework\PersonName\Formatter;
use Magento\NewRelicReporting\Model\Config;

/**
 * Class ReportConcurrentAdmins
 */
class ReportConcurrentAdmins implements ObserverInterface
{
    /**
     * @var Config
     */
    protected $config;

    /**
     * @var \Magento\Backend\Model\Auth\Session
     */
    protected $backendAuthSession;

    /**
     * @var \Magento\NewRelicReporting\Model\UsersFactory
     */
    protected $usersFactory;

    /**
     * @var \Magento\Framework\Json\EncoderInterface
     */
    protected $jsonEncoder;

    /**
     * @var DataObjectFormatter
     */
    private $nameFormatter;

    /**
     * @param Config $config
     * @param \Magento\Backend\Model\Auth\Session $backendAuthSession
     * @param \Magento\NewRelicReporting\Model\UsersFactory $usersFactory
     * @param \Magento\Framework\Json\EncoderInterface $jsonEncoder
     * @param DataObjectFormatter $nameFormatter
     */
    public function __construct(
        Config $config,
        \Magento\Backend\Model\Auth\Session $backendAuthSession,
        \Magento\NewRelicReporting\Model\UsersFactory $usersFactory,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        DataObjectFormatter $nameFormatter = null
    ) {
        $this->config = $config;
        $this->backendAuthSession = $backendAuthSession;
        $this->usersFactory = $usersFactory;
        $this->jsonEncoder = $jsonEncoder;
        $this->nameFormatter = $nameFormatter ?: ObjectManager::getInstance()->get(DataObjectFormatter::class);
    }

    /**
     * Reports concurrent admins to the database reporting_users table
     *
     * @param Observer $observer
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function execute(Observer $observer)
    {
        if ($this->config->isNewRelicEnabled()) {
            if ($this->backendAuthSession->isLoggedIn()) {
                $user = $this->backendAuthSession->getUser();
                $jsonData = [
                    'id' => $user->getId(),
                    'username' => $user->getUserName(),
                    'name' => $this->nameFormatter->format($user, Formatter::FORMAT_DEFAULT),
                ];

                $modelData = [
                    'type' => 'admin_activity',
                    'action' => $this->jsonEncoder->encode($jsonData),
                ];

                /** @var \Magento\NewRelicReporting\Model\Users $usersModel */
                $usersModel = $this->usersFactory->create();
                $usersModel->setData($modelData);
                $usersModel->save();
            }
        }
    }
}
