<?php

declare(strict_types=1);

namespace Inchoo\FAQNotification\Observers;

use Magento\Catalog\Model\ProductRepository;
use Magento\Customer\Model\Session;
use Magento\Framework\Escaper;
use Magento\Framework\Event\Observer;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Store\Model\StoreManagerInterface;

class NotifyAdmin implements \Magento\Framework\Event\ObserverInterface
{
    const ADMIN_NOTIFICATION_EMAIL = 'admin@example.com';

    /**
     * @var Session
     */
    protected $customerSession;

    /**
     * @var ProductRepository
     */
    protected $productRepository;

    /**
     * @var TransportBuilder
     */
    protected $transportBuilder;

    /**
     * @var Escaper
     */
    protected $escaper;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    public function __construct(
        Session $customerSession,
        ProductRepository $productRepository,
        TransportBuilder $transportBuilder,
        Escaper $escaper,
        StoreManagerInterface $storeManager
    ) {
        $this->customerSession = $customerSession;
        $this->productRepository = $productRepository;
        $this->transportBuilder = $transportBuilder;
        $this->escaper = $escaper;
        $this->storeManager = $storeManager;
    }

    /**
     * @param Observer $observer
     * @return $this|void
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\MailException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function execute(Observer $observer): NotifyAdmin
    {
        $questionData = $observer->getData('question');
        $productId = $questionData->getProductId();
        $message = $questionData->getQuestionContent();

        $product = $this->productRepository->getById($productId);
        $senderEmail = $this->getUserEmail();

        $sender = [
            'name' => $this->escaper->escapeHtml($this->getUserName()),
            'email' => $this->escaper->escapeHtml($senderEmail)
        ];
        $transport = $this->transportBuilder->setTemplateOptions(
            [
                'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
                'store' => $this->storeManager->getStore()->getId(),
            ]
        )
            ->setTemplateIdentifier('product_faq_notification')
            ->setTemplateVars(
                [
                'name' => 'Admin',
                'email' => self::ADMIN_NOTIFICATION_EMAIL,
                'product_name' => $product->getName(),
                'product_url' => $product->getUrlInStore(),
                'message' => $message,
                'sender_name' => $sender['name'],
                'sender_email' => $sender['email']
            ]
            )
            ->addTo(self::ADMIN_NOTIFICATION_EMAIL)
            ->setFromByScope($sender)
            ->getTransport();
        try {
            $transport->sendMessage();
        } catch (\Exception $e) {
            __($e->getMessage());
        }

        return $this;
    }

    /**
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function getUserName(): string
    {
        return $this->customerSession->getCustomerData()->getFirstname() .
            ' ' .
            $this->customerSession->getCustomerData()->getLastName();
    }

    /**
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function getUserEmail(): string
    {
        return $this->customerSession->getCustomerData()->getEmail();
    }
}
