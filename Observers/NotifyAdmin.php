<?php

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

    public function execute(Observer $observer)
    {
        $eventData = $observer->getData('question');
        $productId = $eventData->getProductId();
        $faqId = $eventData->getFaqId();
        $message = $eventData->getQuestionContent();
        $storeId = $eventData->getStoreid();

        $product = $this->getProduct($productId);
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

    /** helper methods */
    protected function getProduct($productId)
    {
        $product = $this->productRepository->getById($productId);

        return $product;
    }

    protected function getUserName()
    {
        return $this->customerSession->getCustomerData()->getFirstname() .
            ' ' .
            $this->customerSession->getCustomerData()->getLastName();
    }

    protected function getUserEmail()
    {
        return $this->customerSession->getCustomerData()->getEmail();
    }
}
