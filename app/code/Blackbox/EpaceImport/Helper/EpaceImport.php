<?php

namespace Blackbox\EpaceImport\Helper;

use Magento\Store\Model\ScopeInterface;

class EpaceImport extends \Magento\Framework\App\Helper\AbstractHelper{
    
       /**
     * @var Mage_Customer_Model_Customer[]
     */
    protected $customerCustomerMap = [];

    /**
     * @var Mage_Customer_Model_Customer[]
     */
    protected $salesPersonCustomerMap = [];

    /**
     * @var Mage_Customer_Model_Customer[]
     */
    protected $csrCustomerMap = [];

    /**
     * @var int
     */
    protected $wholesaleGroupId = null;

    /**
     * @var int
     */
    protected $csrGroupId = null;

    /**
     * @var Mage_Catalog_Model_Product
     */
    protected $product = null;

    /**
     * @var Mage_Shipping_Model_Carrier_Interface[]
     */
    protected $carriers = null;

    /**
     * @var callable
     */
    protected $output = null;

    protected $salesRepsOptions = null;

    /**
     * @param callable $output
     * @return $this
     */
    public function setOutput($output)
    {
        $this->output = $output;

        return $this;
    }

    public function getShipViaMethodCode(\Blackbox\Epace\Model\Epace\Ship\Via $shipVia)
    {
        switch ($shipVia->getShipProvider()->getName()) {
            case 'FedEx':
                $methodCode = $shipVia->getData('fedExMethod');
                break;
            case 'UPS':
                $methodCode = $shipVia->getData('uPSMethod');
                break;
        }
        if (empty($methodCode)) {
            $methodCode = $shipVia->getDescription();
        }

        return strtolower(str_replace(' ', '_', $shipVia->getShipProvider()->getName() . '_' . strtolower($methodCode)));
    }

    public function getJobTypes()
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $storeManager = $objectManager->create('\Magento\Store\Model\StoreManagerInterface');
        $data = $storeManager->getValue('epace_import/job/types', \Magento\Store\Model\ScopeInterface::SCOPE_STORE,null);
        if ($data) {
            $data = json_decode($data, true);
        }

        if (!$data || !$data['types']) {
            $rm = $objectManager->create('\Blackbox\Epace\Model\Resource\Epace\Job\Type\Collection');
            $types = $rm->toOptionHash();
            $storeManager->saveConfig('epace_import/job/types', json_encode(['types' => $types]));
            return $types;
        } else {
            return $data['types'];
        }
    }

    public function importEstimate(\Blackbox\Epace\Model\Epace\Estimate $estimate, Blackbox_EpaceImport_Model_Estimate $magentoEstimate = null)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        if (!$magentoEstimate) {
            
            $magentoEstimate = $objectManager->create('\Blackbox\EpaceImport\Model\Estimate');
        }

        $store = $this->getStore();
        $magentoProduct = $this->getProduct();

        foreach ($estimate->getProducts() as $estimateProduct) {
            foreach ($estimateProduct->getParts() as $part) {
                foreach ($part->getQuantities() as $quantity) {
                    $item = $objectManager->create('\Blackbox\EpaceImport\Model\Estimate\Item');
                    $item->setData([
                        'product_id' => $magentoProduct->getId(),
                        'store_id' => $store->getId(),
                        'sku' => $magentoProduct->getSku(),
                        'name' => $estimateProduct->getDescription() . ' - ' . $part->getDescription() . ' - ' . $quantity->getQuantityOrdered(),
                        'description' => $part->getDescription(),
                        'weight' => (float)$quantity->getWeightPerPiece(),
                        'row_weight' => $quantity->getWeightPerPiece() * $quantity->getQuantityOrdered(),
                        'qty' => $quantity->getData('quantityOrdered'),
                        'base_cost' => $quantity->getCost(),
                        'price' => (float)$quantity->getData('pricePerEach') * (float)$quantity->getEstimatePart()->getData('numSigs'),
                        'base_price' => (float)$quantity->getData('pricePerEach') * (float)$quantity->getEstimatePart()->getData('numSigs'),
                        'tax_percent' => $quantity->getTaxEffectivePercent(),
                        'tax_amount' => $quantity->getData('taxAmount'),
                        'base_tax_amount' => $quantity->getData('taxAmount'),
                        'row_total' => $quantity->getData('price'),
                        'base_row_total' => $quantity->getData('price'),
                        'product_type' => $magentoProduct->getTypeId(),
                        'row_total_incl_tax' => $quantity->getData('grandTotal'),
                        'base_row_total_incl_tax' => $quantity->getData('grandTotal'),
                        'epace_estimate_part_id' => $part->getId(),
                        'epace_estimate_quantity_id' => $quantity->getId(),
                    ]);
                    $magentoEstimate->addItem($item);
                }
            }
        }

        $aggregateFields = [
            'base_grand_total' => 'base_row_total_incl_tax',
            'grand_total' => 'row_total_incl_tax',
            'base_subtotal' => 'base_row_total',
            'subtotal' => 'row_total',
            'tax_amount' => 'tax_amount',
            'total_qty' => 'qty',
            'base_subtotal_incl_tax' => 'row_total_incl_tax',
            'subtotal_incl_tax' => 'row_total_incl_tax',
            'base_total_cost' => 'base_cost'
        ];
        $aggregatedFields = [];
        foreach ($aggregateFields as $field => $sourceField) {
            $aggregatedFields[$field] = 0;
        }
        foreach ($magentoEstimate->getAllItems() as $item) {
            foreach ($aggregateFields as $field => $sourceField) {
                $aggregatedFields[$field] += $item->getData($sourceField);
            }
        }

        if ($estimate->getCustomer()) {
            $customer = $this->getCustomerFromCustomer($estimate->getCustomer());
        } else {
            $customer = $objectManager->create('\Magento\Customer\Model\Customer');
        }
        $salesPersonCustomer = $this->getCustomerFromSalesPerson($estimate->getSalesPerson());
        if ($estimate->getCSR()) {
            $csrCustomer = $this->getCustomerFromCSR($estimate->getCSR());
        } else {
            $csrCustomer = null;
        }

        $magentoEstimate->addData([
            'epace_estimate_id' => $estimate->getId(),
            'status' => $estimate->getData('status'),
            'is_virtual' => 0,
            'store_id' => $this->getStore()->getId(),
            'customer_id' => $customer->getId(),
            'sales_person_id' => $salesPersonCustomer->getId(),
            'csr_id' => $csrCustomer ? $csrCustomer->getId() : null,
            'base_to_global_rate' => 1,
            'base_to_estimate_rate' => 1,
            'store_to_base_rate' => 1,
            'store_to_estimate_rate' => 1,
            'increment_id' => 'EPACEESTIMATE_' . $estimate->getId(),
            'base_currency_code' => $this->getStore()->getBaseCurrencyCode(),
            'customer_email' => $customer->getEmail(),
            'customer_firstname' => $customer->getFirstname(),
            'customer_lastname' => $customer->getLastname(),
            'customer_middlename' => $customer->getMiddlename(),
            'customer_prefix' => $customer->getPrefix(),
            'customer_suffix' => $customer->getSuffix(),
            'customer_is_guest' => 0,
            'customer_group_id' => $customer->getGroupId(),
            'global_currency_code' => $this->getStore()->getBaseCurrencyCode(),
            'estimate_currency_code' => $this->getStore()->getBaseCurrencyCode(),
            'store_currency_code' => $this->getStore()->getBaseCurrencyCode(),
            'store_name' => $this->getStore()->getName(),
            'created_at' => $this->getTimestamp($estimate->getEntryDate(), $estimate->getEntryTime()),
            'total_item_count' => count($magentoEstimate->getAllItems()),
            'estimate_number' => $estimate->getEstimateNumber(),
            'version' => $estimate->getEstimateVersionNumber()
        ]);
        $magentoEstimate->addData($aggregatedFields);

        foreach ($estimate->getQuoteLetters() as $quoteLetter) {
            $magentoEstimate->addStatusHistoryComment(implode(PHP_EOL, array_filter([
                $quoteLetter->getSalutation(),
                $quoteLetter->getBody(),
                $quoteLetter->getComment(),
                $quoteLetter->getClosing()
            ])));
        }

        return $magentoEstimate;
    }

    public function importJob(\Blackbox\Epace\Model\Epace\Job $job, \Magento\Sales\Model\Order $order = null, \Blackbox\EpaceImport\Model\Estimate $magentoEstimate = null)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        if (!$order) {
            $order = $objectManager->create('\Magento\Sales\Model\Order');
        }

        if ($job->getEstimateId() && (!$magentoEstimate || $magentoEstimate->getEpaceEstimateId() != $job->getEstimateId())) {
            $magentoEstimate = $objectManager->create('\Blackbox\EpaceImport\Model\Estimate')->load($job->getEstimateId(), 'epace_estimate_id');
        }

        $salesTax = $job->getCustomer()->getSalesTax();
        if ($salesTax && $salesTax->getTaxRate()) {
            $taxPercent = (float)$salesTax->getTaxRate();
        } else {
            $taxPercent = 0;
        }

        $product = $this->getProduct();

        $totalWeight = 0;
//        $estimatedTotalTaxAmount = 0;
//        $partTotalTaxAmount = 0;
        $totalTaxInvoiced = 0;

        foreach ($job->getParts() as $part) {
            $estimatePart = $part->getEstimatePart();
            /** @var \Blackbox\Epace\Model\Epace\Estimate_Quantity $quantity */
            $quantity = null;
            if ($estimatePart) {
                foreach ($estimatePart->getQuantities() as $_quantity) {
                    if ($_quantity->getData('quantityOrdered') == $part->getData('qtyOrdered')) {
                        $quantity = $_quantity;
                        break;
                    }
                }
            }

            $weight = 0;
//            $estimatedTaxAmount = 0;
//            $estimatedTaxPercent = 0;
            if ($quantity) {
                $weight = $quantity->getData('weightPerPiece');
//                $estimatedTaxAmount = $quantity->getTaxAmount();
//                $estimatedTaxPercent = $quantity->getTaxEffectivePercent();
            }

            $totalWeight += $weight;
//            $estimatedTotalTaxAmount += $estimatedTaxAmount;

            if ($salesTax && $salesTax->getTaxRate()) {
                $taxPercent = (float)$salesTax->getTaxRate();
                $taxAmount = $part->getQuotedPrice() * $taxPercent;
            } else {
                $taxPercent = 0;
                $taxAmount = 0;
            }
//            $partTotalTaxAmount += $taxAmount;

            $qtyInvoiced = 0;
            $qtyShipped = 0;
            $rowInvoiced = 0;
            $rowTaxInvoiced = 0;
            foreach ($part->getInvoices() as $invoice) {
                $rowInvoiced += $invoice->getInvoiceAmount();
                $rowTaxInvoiced += $invoice->getTaxAmount();
                foreach ($invoice->getLines() as $line) {
                    $qtyInvoiced += (float)$line->getQtyInvoiced();
                    $qtyShipped += (float)$line->getQtyShipped();
                }
            }
            $totalTaxInvoiced += $rowTaxInvoiced;

            $price = $part->getValue() / $part->getQtyOrdered();
            $estimatedPrice = $part->getEstimatedCost() / $part->getQtyOrdered();

            $priceInclTax = $price + $taxAmount / $part->getQtyOrdered();

            if ($part->getProduct()) {
                $name = $part->getProduct()->getDescription() . ' ' . $part->getDescription();
            } else {
                $name = $part->getDescription();
            }

            $itemDiscount = 0;
            $itemDiscountPercent = 0;
//            if ($part->getQuotedPrice() < $part->getEstimatedCost()) {
//                $itemDiscount = $part->getEstimatedCost() - $part->getQuotedPrice();
//                $itemDiscountPercent = $itemDiscount / $part->getEstimatedCost();
//            } else {
//                $itemDiscount = 0;
//                $itemDiscountPercent = 0;
//            }

            $item = $objectManager->create('\Magento\Sales\Model\Order\Item');
            $item->setData([
                'store_id' => $this->getStore()->getId(),
                'product_id' => $product->getId(),
                'product_type' => $product->getTypeId(),
                'weight' => $weight,
                'sku' => $product->getSku(),
                'name' => $name,
                'qty_canceled' => 0,
                'qty_invoiced' => $qtyInvoiced,
                'qty_ordered' => $part->getQtyOrdered(),
                'qty_refunded' => 0,
                'qty_shipped' => $qtyShipped,
                'price' => $price,
                'base_price' => $price,
                'original_price' => $estimatedPrice,
                'base_original_price' => $estimatedPrice,
                'tax_percent' => $taxPercent,
                'tax_amount' => $taxAmount,
                'base_tax_amount' => $taxAmount,
                'tax_invoiced' => $rowTaxInvoiced,
                'base_tax_invoiced' => $rowTaxInvoiced,
                'discount_percent' => $itemDiscountPercent,
                'discount_amount' => $itemDiscount,
                'base_discount_amount' => $itemDiscount,
                'discount_invoiced' => 0,
                'base_discount_invoiced' => 0,
                'amount_refunded' => 0,
                'base_amount_refunded' => 0,
                'row_total' => $part->getValue(),
                'base_row_total' => $part->getValue(),
                'row_invoiced' => $rowInvoiced,
                'base_row_invoiced' => $rowInvoiced,
                'row_weight' => $weight * $part->getQtyOrdered(),
                'price_incl_tax' => $priceInclTax,
                'base_price_incl_tax' => $priceInclTax,
                'row_total_incl_tax' => $part->getValue() + $taxAmount,
                'base_row_total_incl_tax' => $part->getValue() + $taxAmount,
                'epace_job_part' => $part->getJobPart(),
                'epace_part_original_price' => $part->getOriginalQuotedPrice(),
                'job_type' => $part->getJobType()
            ]);
            $order->addItem($item);
        }

        $shippingMethod = $this->getShippingMethod($job->getShipVia());

        $customer = $this->getCustomerFromCustomer($job->getCustomer());
        $salesPersonCustomer = $this->getCustomerFromSalesPerson($job->getSalesPerson());
        if ($job->getCSR()) {
            $csrCustomer = $this->getCustomerFromCSR($job->getCSR());
        } else {
            $csrCustomer = null;
        }

        $subTotal = $job->getAmountToInvoice();

        $totalTaxAmount = $subTotal * $taxPercent;

        $shippingAmount = $job->getFreightAmountTotal();
        $shippingTaxAmount = $markup = $shippingAmount * 0.4;
        $shippingInclTax = $shippingAmount + $shippingTaxAmount;

        // some jobs have empty amountInvoiced
        $amountInvoiced = 0;
        $shippingInvoiced = 0;
        foreach ($job->getInvoices() as $invoice) {
            $amountInvoiced += (float)$invoice->getInvoiceAmount();
            $shippingInvoiced += (float)$invoice->getFreightAmount();
        }

        $invoicedCost = 0;
        foreach ($job->getInvoices() as $invoice) {
            $invoicedCost += (float)$invoice->getTotalCost();
        }

        $grandTotal = $subTotal + $totalTaxAmount + $shippingInclTax;
        if ($job->getAmountToInvoice() < $job->getOriginalQuotedPrice()) {
            $discountAmount = $job->getOriginalQuotedPrice() - $job->getAmountToInvoice();
        } else {
            $discountAmount = 0;
        }

        $order->addData([
            'estimate_id' => $magentoEstimate ? $magentoEstimate->getId() : null,
            'epace_job_id' => $job->getId(),
            'shipping_description' => $shippingMethod->getCarrierTitle() . ' - ' . $shippingMethod->getMethodTitle(),
            'is_virtual' => 0,
            'store_id' => $this->getStore()->getId(),
            'customer_id' => $customer->getId(),
            'sales_person_id' => $salesPersonCustomer->getId(),
            'csr_id' => $csrCustomer ? $csrCustomer->getId() : null,
            'base_discount_amount' => $discountAmount,
            'base_grand_total' => $grandTotal,
            'base_shipping_amount' => $shippingAmount,
            'base_shipping_invoiced' => $shippingInvoiced,
            'base_shipping_tax_amount' => $shippingTaxAmount,
            'base_subtotal' => $subTotal,
            'base_subtotal_invoiced' => min($amountInvoiced, $subTotal),
            'base_tax_amount' => $totalTaxAmount,
            'base_tax_invoiced' => $totalTaxInvoiced,
            'base_to_global_rate' => 1,
            'base_to_order_rate' => 1,
            'base_total_invoiced' => $amountInvoiced,
            'base_total_invoiced_cost' => $invoicedCost,
            'base_total_paid' => $amountInvoiced,
            'discount_amount' => $discountAmount,
            'grand_total' => $grandTotal,
            'shipping_amount' => $shippingAmount,
            'shipping_invoiced' => $shippingInvoiced,
            'shipping_tax_amount' => $shippingTaxAmount,
            'store_to_base_rate' => 1,
            'store_to_order_rate' => 1,
            'subtotal' => $subTotal,
            'subtotal_invoiced' => min($amountInvoiced, $subTotal),
            'tax_amount' => $totalTaxAmount,
            'tax_invoiced' => $totalTaxInvoiced,
            'total_invoiced' => $amountInvoiced,
            'total_paid' => $amountInvoiced,
            'total_qty_ordered' => $job->getQtyOrdered(),
            'customer_is_guest' => 0,
            'customer_note_notify' => 0,
            'billing_address_id' => null,
            'customer_group_id' => $customer->getGroupId(),
            'email_sent' => 1,
            'base_subtotal_incl_tax' => $subTotal + $totalTaxAmount,
            'subtotal_incl_tax' => $subTotal + $totalTaxAmount,
            'weight' => $totalWeight,
            'increment_id' => 'EPACEJOB_' . $job->getJob(),
            'base_currency_code' => $this->getStore()->getBaseCurrencyCode(),
            'customer_email' => $customer->getEmail(),
            'customer_firstname' => $customer->getFirstname(),
            'customer_lastname' => $customer->getLastname(),
            'customer_middlename' => $customer->getMiddlename(),
            'customer_prefix' => $customer->getPrefix(),
            'customer_suffix' => $customer->getSuffix(),
            'global_currency_code' => $this->getStore()->getBaseCurrencyCode(),
            'order_currency_code' => $this->getStore()->getBaseCurrencyCode(),
            'shipping_method' => $shippingMethod->getCarrier() . '_' . $shippingMethod->getMethod(),
            'store_currency_code' => $this->getStore()->getBaseCurrencyCode(),
            'store_name' => $this->getStore()->getName(),
            'created_at' => $this->getTimestamp($job->getDateSetup(), $job->getTimeSetUp()),
            'total_item_count' => count($job->getParts()),
            'shipping_incl_tax' => $shippingInclTax,
            'base_shipping_incl_tax' => $shippingInclTax,
            'job_value' => $job->getJobValue(),
            'job_type' => $job->getTypeId(),
            'base_markup' => $markup,
            'markup' => $markup,
            'amount_to_invoice' => $job->getAmountToInvoice(),
            'change_order_total' => $job->getChangeOrderTotal(),
            'original_quoted_price' => $job->getOriginalQuotedPrice(),
            'epace_bill_to_job_contact' => $job->getBillToJobContactId(),
            'epace_ship_to_job_contact' => $job->getShipToJobContactId(),
            'epace_job_description' => $job->getDescription()
        ]);

        $this->setOrderStatus($order, $job->getAdminStatusCode());

        $contacts = [];

        $addedContacts = [];
        $shippingAddressAdded = false;
        $billingAddressAdded = false;
        foreach ($job->getJobContacts() as $jobContact) {
            $contactId = $jobContact->getContactId();
            if (!$jobContact->getContact()) {
                continue;
            }
            if (!isset($contacts[$contactId])) {
                $contacts[$contactId] = [
                    'jobContact' => $jobContact,
                    'contact' => $jobContact->getContact(),
                    'billing' => $jobContact->getBillTo(),
                    'shipping' => $jobContact->getShipTo()
                ];
            } else {
                $contacts[$contactId]['billing'] |= $jobContact->getBillTo();
                $contacts[$contactId]['shipping'] |= $jobContact->getShipTo();
            }
        }

        foreach ($contacts as $contactId => $contactData) {
            if ($contactData['shipping']) {
                $type = 'shipping';
                $shippingAddressAdded = true;
            } else if ($contactData['billing']) {
                $type = 'billing';
                $billingAddressAdded = true;
            } else {
                continue;
            }
            /** @var \Blackbox\Epace\Model\Epace\Contact $contact */
            $contact = $contactData['contact'];

            $this->addAddressToOrder($order, $contact, $type, $contactData['jobContact']);

            $addedContacts[] = $contact->getId();
        }

        if (!$shippingAddressAdded) {
            if (!empty($contacts)) {
                foreach ($contacts as $contactId => $contactData) {
                    /** @var \Blackbox\Epace\Model\Epace\Contact $contact */
                    $contact = $contactData['contact'];
                    $this->addAddressToOrder($order, $contact, 'shipping', $contactData['jobContact']);
                    $addedContacts[] = $contact->getId();
                    break;
                }
            } else {
                $order->addAddress($objectManager->create('\Magento\Sales\Model\Order\Address')->addData([
                    'customer_id' => $customer->getId(),
                    'address_type' => 'shipping'
                ]));
                $this->writeln('--------------------------EMPTY SHIPPING ADDRESS ADDED-----------------------');
            }
        }

        if (!$billingAddressAdded) {
            $shipping = false;
            $contact = null;
            foreach ($contacts as $contactId => $contactData) {
                if (is_null($contact)) {
                    $contact = $contactData['contact'];
                } else if ($contactData['billing']) {
                    $contact = $contactData['contact'];
                    break;
                } else if ($contactData['shipping'] && !$shipping) {
                    $contact = $contactData['contact'];
                    $shipping = true;
                }
            }

            if ($contact && isset($contactData)) {
                $this->addAddressToOrder($order, $contact, 'billing', $contactData['jobContact']);
                $addedContacts[] = $contact->getId();
            } else {
                $order->addAddress($objectManager->create('\Magento\Sales\Model\Order\Address')->addData([
                    'customer_id' => $customer->getId(),
                    'address_type' => 'billing'
                ]));
                $this->writeln('--------------------------EMPTY BILLING ADDRESS ADDED-----------------------');
            }
        }

        /** @var \Magento\Sales\Model\Order\Payment $payment */
        $payment = $objectManager->create('\Magento\Sales\Model\Order\Payment');
        $payment->setMethod('epace_payment');
        $order->setPayment($payment);

        foreach ($job->getNotes() as $note) {
            $order->addStatusHistoryComment($note->getNote());
        }

        return $order;
    }

    public function importInvoice(\Blackbox\Epace\Model\Epace\Invoice $invoice, \Magento\Sales\Model\Order $order = null, \Magento\Sales\Model\Order\Invoice $magentoInvoice = null)
    {
        if (!$order) {
            $order = $objectManager->create('\Magento\Sales\Model\Order')->load($invoice->getData('job'), 'epace_job_id');
            if (!$order->getId()) {
                throw new \Exception('Unable to find order for invoice. Invoice id: ' . $invoice->getId() . '. Job: ' . $invoice->getJobId());
            }
        }

        if (!$magentoInvoice) {
            $magentoInvoice = $objectManager->create('\Magento\Sales\Model\Order\Invoice');
        }


//        $qty = 0;
//        foreach ($invoice->getLines() as $line) {
//            $qty += (float)$line->getQtyInvoiced();
//        }
        $qty = $invoice->getJobPart()->getQtyOrdered();

        $discount = 0;
        if ($invoice->getReceivable()) {
            $discount = $invoice->getReceivable()->getDiscountApplied();
        } else {
            foreach ($invoice->getExtras() as $extra) {
                if ($extra->getType()->getExtraCategory() == \Blackbox\Epace\Model\Epace\Invoice\Extra\Type::EXTRA_CATEGORY_TYPE_DISCOUNT) {
                    $discount += (float)$extra->getPrice();
                }
            }
        }

        $billingAddressId = null;
        $shippingAddressId = null;

        $shipToContact = $invoice->getData('shipToContact');
        $billToContact = $invoice->getData('billToContact');

        if (!empty($shipToContact)) {
            /** @var \Magento\Sales\Model\Order\Address $address */
            foreach ($order->getAddressesCollection() as $address) {
                if ($address->isDeleted() || $address->getAddressType() != \Magento\Sales\Model\Order\Address::TYPE_SHIPPING) {
                    continue;
                }
                if ($address->getEpaceContactId() == $shipToContact) {
                    $shippingAddressId = $address->getId();
                    break;
                }
            }
        }

        if (!empty($billToContact)) {
            /** @var \Magento\Sales\Model\Order\Address $address */
            foreach ($order->getAddressesCollection() as $address) {
                if ($address->isDeleted() || $address->getAddressType() != \Magento\Sales\Model\Order\Address::TYPE_BILLING) {
                    continue;
                }
                if ($address->getEpaceContactId() == $billToContact) {
                    $billingAddressId = $address->getId();
                    break;
                }
            }
        }

        $magentoInvoice->setData([
            'store_id' => $this->getStore()->getId(),
            'base_grand_total' => $invoice->getInvoiceAmount(),
            'shipping_tax_amount' => 0,
            'tax_amount' => $invoice->getTaxAmount(),
            'base_tax_amount' => $invoice->getTaxAmount(),
            'store_to_order_rate' => 1,
            'base_shipping_tax_amount' => 0,
            'base_discount_amount' => $discount,
            'base_to_order_rate' => 1,
            'grand_total' => $invoice->getInvoiceAmount(),
            'shipping_amount' => $invoice->getFreightAmount(),
            'subtotal_incl_tax' => (float)$invoice->getLineItemTotal() + (float)$invoice->getTaxAmount(),
            'base_subtotal_incl_tax' => (float)$invoice->getLineItemTotal() + (float)$invoice->getTaxAmount(),
            'store_to_base_rate' => 1,
            'base_shipping_amount' => $invoice->getFreightAmount(),
            'total_qty' => $qty,
            'base_to_global_rate' => 1,
            'subtotal' => $invoice->getLineItemTotal(),
            'base_subtotal' => $invoice->getLineItemTotal(),
            'discount_amount' => $discount,
            'billing_address_id' => $billingAddressId,
            'order_id' => $order->getId(),
            'state' => \Magento\Sales\Model\Order\Invoice::STATE_PAID,
            'shipping_address_id' => $shippingAddressId,
            'store_currency_code' => $this->getStore()->getBaseCurrencyCode(),
            'order_currency_code' => $this->getStore()->getBaseCurrencyCode(),
            'base_currency_code' => $this->getStore()->getBaseCurrencyCode(),
            'global_currency_code' => $this->getStore()->getBaseCurrencyCode(),
            'increment_id' => 'EPACEINVOICE_' . $invoice->getId(),
            'created_at' => $this->getTimestamp($invoice->getDateSetup(), $invoice->getTimeSetup()),
            'hidden_tax_amount' => 0,
            'base_hidden_tax_amount' => 0,
            'shipping_hidden_tax_amount' => 0,
            'base_shipping_hidden_tax_amnt' => null,
            'shipping_incl_tax' => $invoice->getFreightAmount(),
            'base_shipping_incl_tax' => $invoice->getFreightAmount(),
            'epace_invoice_id' => $invoice->getId(),
            'epace_invoice_number' => $invoice->getInvoiceNum()
        ]);

        $orderItem = null;
        foreach ($order->getAllItems() as $_item) {
            if ($_item->getEpaceJobPart() == $invoice->getJobPartNumber()) {
                $orderItem = $_item;
                break;
            }
        }

        if (!$orderItem) {
            throw new \Exception('Unable to find order item for invoice. Order id: ' . $order->getId() . '. Job: ' . $invoice->getJobId() . '. Invoice epace id: ' . $invoice->getId());
        }
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        foreach ($invoice->getLines() as $line) {
            /** @var \Magento\Sales\Model\Order\Invoice_Item $invoiceItem */
            $invoiceItem = $objectManager->create('Magento\Sales\Order\Invoice\Item');
            $invoiceItem->setData([
                'base_price' => 0,
                'tax_amount' => 0,
                'base_row_total' => $line->getTotalPrice(),
                'discount_amount' => null,
                'row_total' => $line->getTotalPrice(),
                'base_discount_amount' => null,
                'price_incl_tax' => 0,
                'base_tax_amount' => 0,
                'base_price_incl_tax' => 0,
                'qty' => $line->getQtyOrdered(),
                'base_cost' => null,
                'price' => 0,
                'base_row_total_incl_tax' => $line->getTotalPrice(),
                'row_total_incl_tax' => $line->getTotalPrice(),
                'product_id' => $orderItem->getProductId(),
                'order_item_id' => $orderItem->getId(),
                'sku' => $orderItem->getSku(),
                'name' => $line->getDescription(),
                'hidden_tax_amount' => 0,
                'base_hidden_tax_amount' => 0,
                'base_weee_tax_applied_amount' => 0,
                'base_weee_tax_applied_row_amnt' => 0,
                'weee_tax_applied_amount' => 0,
                'weee_tax_applied_row_amount' => 0,
                'weee_tax_disposition' => 0,
                'weee_tax_row_disposition' => 0,
                'base_weee_tax_disposition' => 0,
                'base_weee_tax_row_disposition' => 0,
                'epace_invoice_line_id' => $line->getId()
            ]);

            $magentoInvoice->addItem($invoiceItem);
        }

        if ($invoice->getReceivable()) {
            $receivable = $objectManager->create('\Blackbox\EpaceImport\Model\Receivable')->load($invoice->getReceivableId(), 'epace_receivable_id');
            if (!$receivable->getId()) {
                $receivable = $this->importReceivable($invoice->getReceivable(), $order);
                $receivable->save();
                $this->writeln('Created Receivable ' . $receivable->getId() . ' epace id: ' . $invoice->getReceivable()->getId());
            }
            $magentoInvoice->addData([
                'receivable_id' => $receivable->getId(),
                'epace_receivable_id' => $invoice->getReceivableId()
            ]);
        }

        return $magentoInvoice;
    }

    public function importReceivable(\Blackbox\Epace\Model\Epace\Receivable $receivable, \Magento\Sales\Model\Order $order = null, \Blackbox\EpaceImport\Model\Receivable $magentoReceivable = null)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $customer = $this->getCustomerFromCustomer($receivable->getCustomer());

        $orderId = null;
        if ($receivable->getJobId()) {
            if (!$order || !$order->getId() || $order->getEpaceJobId() != $receivable->getJobId()) {
                $order = $objectManager->create('\Magento\Sales\Model\Order')->load($receivable->getJobId(), 'epace_job_id');
                if (!$order->getId()) {
                    throw new \Exception('Unable to find order with job ' . $receivable->getJobId() . ' for receivable ' . $receivable->getId());
                }
            }
            $orderId = $order->getId();
        }

        if (!$magentoReceivable) {
            $magentoReceivable = $objectManager('\Blackbox\EpaceImport\Model\Receivable');
        }
        $magentoReceivable->setData([
            'store_id' => $this->getStore()->getId(),
            'customer_id' => $customer->getId(),
            'base_grand_total' => $receivable->getInvoiceAmount(),
            'shipping_tax_amount' => 0,
            'tax_amount' => $receivable->getTaxAmount(),
            'base_tax_amount' => $receivable->getTaxAmount(),
            'store_to_order_rate' => 1,
            'base_shipping_tax_amount' => 0,
            'base_discount_amount' => $receivable->getDiscountApplied(),
            'base_to_order_rate' => 1,
            'grand_total' => $receivable->getInvoiceAmount(),
            'shipping_amount' => $receivable->getFreightAmount(),
            'subtotal_incl_tax' => (float)$receivable->getOriginalAmount() + (float)$receivable->getTaxAmount(),
            'base_subtotal_incl_tax' => (float)$receivable->getOriginalAmount() + (float)$receivable->getTaxAmount(),
            'store_to_base_rate' => 1,
            'base_shipping_amount' => $receivable->getFreightAmount(),
            'base_to_global_rate' => 1,
            'subtotal' => $receivable->getOriginalAmount(),
            'base_subtotal' => $receivable->getOriginalAmount(),
            'discount_amount' => $receivable->getDiscountApplied(),
            'order_id' => $orderId,
            'state' => $receivable->getStatus(),
            'store_currency_code' => $receivable->getAltCurrency(),
            'order_currency_code' => $this->getStore()->getBaseCurrencyCode(),
            'base_currency_code' => $this->getStore()->getBaseCurrencyCode(),
            'global_currency_code' => $this->getStore()->getBaseCurrencyCode(),
            'increment_id' => 'EPACERECEIVABLE_' . $receivable->getId(),
            'customer_firstname' => $receivable->getContactFirstName(),
            'customer_lastname' => $receivable->getContactLastName(),
            'customer_email' => $customer->getEmail(),
            'customer_group_id' => $customer->getGroupId(),
            'created_at' => $this->getTimestamp($receivable->getDateSetup(), $receivable->getTimeSetup()),
            'hidden_tax_amount' => 0,
            'base_hidden_tax_amount' => 0,
            'shipping_hidden_tax_amount' => 0,
            'base_shipping_hidden_tax_amnt' => null,
            'shipping_incl_tax' => $receivable->getFreightAmount(),
            'base_shipping_incl_tax' => $receivable->getFreightAmount(),
            'unpaid_amount' => $receivable->getUnpaidAmount(),
            'invoice_date' => strtotime($receivable->getInvoiceDate()),
            'due_date' => strtotime($receivable->getDueDate()),
            'expected_payment_date' => strtotime($receivable->getExpectedPaymentDate()),
            'date_paid_off' => $receivable->getDatePaidOff() ? strtotime($receivable->getDatePaidOff()) : null,
            'description' => $receivable->getDescription(),
            'gl_register_number' => $receivable->getGlRegisterNumber(),
            'epace_receivable_id' => $receivable->getId(),
            'invoice_number' => $receivable->getInvoiceNumber()
        ]);

        return $magentoReceivable;
    }

    public function importShipment(\Blackbox\Epace\Model\Epace\Job_Shipment $jobShipment, \Magento\Sales\Model\Order $order = null, \Magento\Sales\Model\Order\Shipment $orderShipment = null)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        if (!$order) {
            $order = $objectManager->create('\Magento\Sales\Model\Order')->load($jobShipment->getJobId(), 'epace_job_id');
            if (!$order->getId()) {
                throw new \Exception('Unable to find order for shipment. JobShipment id: ' . $jobShipment->getId() . '. Job: ' . $jobShipment->getJobId() . '.');
            }
        }

        if (!$orderShipment) {
            $orderShipment = $objectManager->create('\Magento\Sales\Model\Order\Shipment');
        }

        $shippedOrderItems = [];

        $addShipOrderItem = function (\Magento\Sales\Model\Order\Item $item, \Blackbox\Epace\Model\Epace\Carton\Content $content) use (&$shippedOrderItems) {
            if (!isset($shippedOrderItems[$item->getId()])) {
                $shippedOrderItems[$item->getId()] = [
                    'orderItem' => $item,
                    'qty' => $content->getQuantity()
                ];
            } else {
                $shippedOrderItems[$item->getId()]['qty'] += $content->getQuantity();
            }
        };

        $shippingMethod = $this->getShippingMethod($jobShipment->getShipVia());

        foreach ($jobShipment->getCartons() as $carton) {
            foreach ($carton->getContents() as $content) {
                $partId = false;
                if ($item = $content->getJobPartItem()) {
                    $partId = $item->getJobPartNumber();
                } else if ($material = $content->getJobMaterial()) {
                    $partId = $material->getJobPartNumber();
                } else if ($component = $content->getJobComponent()) {
                    $partId = $component->getJobPartNumber();
                } else if ($pressForm = $content->getJobPartPressForm()) {
                    $partId = $pressForm->getJobPartNumber();
                } else if ($material = $content->getJobMaterial()) {
                    $partId = $material->getJobPartNumber();
                } else if ($part = $content->getJobPart()) {
                    $partId = $part->getJobPart();
                }

                if ($partId) {
                    foreach ($order->getAllItems() as $item) {
                        if ($item->getEpaceJobPart() == $partId) {
                            $addShipOrderItem($item, $content);
                        }
                    }
                } else if ($product = $content->getJobProduct()) {
                    $partIds = [];
                    foreach ($product->getParts() as $part) {
                        $partIds[] = $part->getJobPart();
                    }
                    foreach ($order->getAllItems() as $item) {
                        if (in_array($item->getEpaceJobPart(), $partIds)) {
                            $addShipOrderItem($item, $content);
                        }
                    }
                } else {
                    $contentJob = null;
                    if ($content->getJobPartJob()) {
                        $contentJob = $content->getJobPartJob();
                    } else if ($content->getJob()) {
                        $contentJob = $content->getJob();
                    }
                    if ($contentJob && $contentJob->getId() != $jobShipment->getJob()->getId()) {
                        throw new \Exception("Shipment ({$jobShipment->getId()}) job ({$jobShipment->getJob()->getId()}) do not match with content ({$content->getId()}) job ({$contentJob->getId()}).");
                    }
                    $job = $jobShipment->getJob();
                    foreach ($job->getProducts() as $product) {
                        foreach ($product->getParts() as $part) {
                            $found = false;
                            foreach ($order->getAllItems() as $item) {
                                if ($item->getEpaceJobPart() == $part->getJobPart()) {
                                    $addShipOrderItem($item, $content);
                                    $found = true;
                                    break;
                                }
                            }
                            if (!$found) {
                                throw new \Exception('Not found matching order items for shipment content ' . $content->getId());
                            }
                        }
                    }
                }
            }

            if ($carton->getTrackingNumber()) {
                /** @var \Magento\Sales\Model\Order\Shipment_Track $track */
                $track = $objectManager->create('\Magento\Sales\Model\Order\Shipment\Track');
                if ($shippingMethod->getCarrier() == 'epace_shipping') {
                    $title = $shippingMethod->getCarrierTitle() . ': ' . $jobShipment->getShipVia()->getShipProvider()->getName() . ' - ' . $jobShipment->getShipVia()->getDescription();
                } else {
                    $title = $shippingMethod->getCarrierTitle() . ' - ' . $shippingMethod->getMethodTitle();
                }
                $track->setData([
                    'weight' => $carton->getWeight(),
                    'qty' => $carton->getTotalSkidQuantity(),
                    'order_id' => $order->getId(),
                    'track_number' => $carton->getTrackingNumber(),
                    'description' => $carton->getTrackingLink(),
                    'title' => $title,
                    'carrier_code' => $shippingMethod->getCarrier(),
                    'created_at' => $this->getTimestamp($carton->getActualDate(), $carton->getActualTime()),
                ]);
                $orderShipment->addTrack($track);
            }
        }

        if (empty($orderShipment->getAllItems())) {
            foreach ($order->getAllItems() as $item) {
                $shippedOrderItems[$item->getId()] = [
                    'orderItem' => $item,
                    'qty' => 0
                ];
            }
        }

        foreach ($shippedOrderItems as $shippedOrderItem) {
            /** @var \Magento\Sales\Model\Order\Item $orderItem */
            $orderItem = $shippedOrderItem['orderItem'];
            $shipmentItem = $objectManager->create('\Magento\Sales\Model\Order\Shipment\Item');
            $shipmentItem->setData([
                'price' => $orderItem->getPrice(),
                'weight' => $orderItem->getWeight(),
                'qty' => $shippedOrderItem['qty'],
                'product_id' => $orderItem->getProductId(),
                'order_item_id' => $orderItem->getId(),
                'name' => $orderItem->getName(),
                'sku' => $orderItem->getSku()
            ]);
            $orderShipment->addItem($shipmentItem);
        }

        if ($jobShipment->getContact() && $jobShipment->getContact()->getCustomer()) {
            $customer = $this->getCustomerFromCustomer($jobShipment->getContact()->getCustomer());
            $customerId = $customer->getId();
        } else {
            $customerId = $order->getCustomerId();
        }

        if ($jobShipment->getContact() && $jobShipment->getContact()->getSalesPerson()) {
            $salesPersonCustomer = $this->getCustomerFromSalesPerson($jobShipment->getContact()->getSalesPerson());
            $salesPersonCustomerId = $salesPersonCustomer->getId();
        } else {
            $salesPersonCustomerId = $order->getSalesPersonId();
        }

        $shippingAddressId = null;
        /** @var \Magento\Sales\Model\Order\Address $address */
        foreach ($order->getAddressesCollection() as $address) {
            if ($address->isDeleted() || $address->getAddressType() != \Magento\Sales\Model\Order\Address::TYPE_SHIPPING) {
                continue;
            }
            if ($address->getEpaceContactId() == $jobShipment->getContactId()) {
                $shippingAddressId = $address->getId();
                break;
            }
        }

        $billingAddressId = null;
        if ($epaceContactId = $jobShipment->getShipBillToContactId()) {
            /** @var \Magento\Sales\Model\Order\Address $address */
            foreach ($order->getAddressesCollection() as $address) {
                if ($address->isDeleted() || $address->getAddressType() != \Magento\Sales\Model\Order\Address::TYPE_BILLING) {
                    continue;
                }
                if ($address->getEpaceContactId() == $epaceContactId) {
                    $billingAddressId = $address->getId();
                    break;
                }
            }
        } else {
            $billingAddressId = $order->getBillingAddressId();
        }

//        $shippingPrice = 0;
//        $shippingCost = 0;
//        if ($shippingAddressId && ($shippingMethod->getCarrier() == 'ups' || $shippingMethod->getCarrier() == 'fedex')) {
//            $shippingAddress = null;
//            foreach ($order->getAddressesCollection() as $address) {
//                if ($address->getId() == $shippingAddressId) {
//                    $shippingAddress = $address;
//                }
//            }
//            if ($shippingAddress) {
//                $carrier = $this->getCarrier($shippingMethod->getCarrier());
//                if ($carrier instanceof Mage_Usa_Model_Shipping_Carrier_Ups || $carrier instanceof Mage_Usa_Model_Shipping_Carrier_Fedex) {
//                    /** @var Mage_Shipping_Model_Rate_Request $request */
//                    $request = Mage::getModel('shipping/rate_request');
//                    $request->setLimitMethod($shippingMethod->getMethod());
//                    $request->setDestCountryId($shippingAddress->getCountryId());
//                    $request->setDestRegionId($shippingAddress->getRegionId());
//                    $request->setDestRegionCode($shippingAddress->getRegionCode());
//                    $request->setDestCity($shippingAddress->getCity());
//                    $request->setDestPostcode($shippingAddress->getPostcode());
//                    $request->setDestStreet($shippingAddress->getStreetFull());
//                    $request->setPackageWeight($jobShipment->getWeight());
//                    $request->setBaseCurrency($this->getStore()->getBaseCurrencyCode());
//
//                    $result = $carrier->collectRates($request);
//                    if (!$result->getError()) {
//                        var_dump($result->getAllRates());
//                        foreach ($result->getAllRates() as $rate) {
//                            if ($rate->getMethod() == $shippingMethod->getMethod()) {
//                                $shippingPrice = $rate->getPrice();
//                                $shippingCost = $rate->getCost();
//                            }
//                        }
//                    } else {
//                        var_dump($result->getError());
//                    }
//                }
//            }
//        }

        $orderShipment->setData([
            'store_id' => $this->getStore()->getId(),
            'total_weight' => $jobShipment->getWeight(),
            'total_qty' => $jobShipment->getQuantity(),
            'email_sent' => null,
            'order_id' => $order->getId(),
            'customer_id' => $customerId,
            'sales_person_id' => $salesPersonCustomerId,
            'shipping_address_id' => $shippingAddressId,
            'billing_address_id' => $billingAddressId,
            //'shipment_status' => '',
            'increment_id' => 'EPACESHIPMENT_' . $jobShipment->getId(),
            'created_at' => $this->getTimestamp($jobShipment->getDate(), $jobShipment->getTime()),
            'packages' => null,
            'shipping_label' => null,
            'epace_shipment_id' => $jobShipment->getId(),
//            'carrier' => $shippingMethod->getCarrier(),
//            'carrier_title' => $shippingMethod->getCarrierTitle(),
//            'method' => $shippingMethod->getMethod(),
//            'method_title' => $shippingMethod->getTitle(),
//            'price' => $shippingPrice,
//            'cost' => $shippingCost
        ]);

        return $orderShipment;
    }

    public function importPurchaseOrder(\Blackbox\Epace\Model\Epace\Purchase\Order $purchaseOrder, \Blackbox\EpaceImport\Model\PurchaseOrder $mpo = null)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        if (!$mpo instanceof \Blackbox\EpaceImport\Model\PurchaseOrder) {
            $mpo = $objectManager->create('Blackbox\EpaceImport\Model\PurchaseOrder');
        }

        if ($purchaseOrder->getShipVia()) {
            $shippingRateResult = $this->getShippingMethod($purchaseOrder->getShipVia());
            $shippingMethod = $shippingRateResult->getCarrier() . '_' . $shippingRateResult->getMethod();
            $shippingDescription = $shippingRateResult->getCarrierTitle() . ' - ' . $shippingRateResult->getMethodTitle();
        } else {
            $shippingMethod = null;
            $shippingDescription = null;
        }

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        if ($purchaseOrder->getCountry() && $purchaseOrder->getStateCode()) {
            /** @var Mage_Directory_Model_Resource_Region_Collection $regionCollection */
            $regionCollection = $objectManager->create('Magento\Directory\Model\ResourceModel\Region\Collection');
            $regionCollection->addFieldToFilter('country_id', $purchaseOrder->getCountry()->getIsoCountry())
                ->addFieldToFilter('code', $purchaseOrder->getStateCode());
            if ($region = $regionCollection->getFirstItem()) {
                $regionName = $region->getDefaultName();
                $regionId = $region->getId();
            } else {
                $regionName = $purchaseOrder->getStateCode();
                $regionId = null;
            }
        } else {
            $regionName = $purchaseOrder->getStateCode();
            $regionId = null;
        }

        $mpo->setData([
            'epace_purchase_order_id' => $purchaseOrder->getId(),
            'status' => $purchaseOrder->getOrderStatusId(),
            'type' => $purchaseOrder->getPurchaseOrderType(),
            'shipping_description' => $shippingDescription,
            'requester' => $purchaseOrder->getRequester(),
            'store_id' => $this->getStore()->getId(),
            'base_discount_amount' => $purchaseOrder->getDiscountAmount(),
            'base_grand_total' => $purchaseOrder->getTaxedTotal(),
            'base_shipping_tax_amount' => 0,
            'base_subtotal' => $purchaseOrder->getOrderTotal(),
            'base_original_total' => $purchaseOrder->getOriginalTotal(),
            'base_tax_amount' => $purchaseOrder->getTaxAmount1(),
            'base_tax_amount2' => $purchaseOrder->getTaxAmount2(),
            'discount_amount' => $purchaseOrder->getDiscountAmount(),
            'grand_total' => $purchaseOrder->getTaxedTotal(),
            'base_to_global_rate' => 1,
            'base_to_order_rate' => 1,
            'subtotal' => $purchaseOrder->getOrderTotal(),
            'original_total' => $purchaseOrder->getOriginalTotal(),
            'tax_amount' => $purchaseOrder->getTaxAmount1(),
            'tax_amount2' => $purchaseOrder->getTaxAmount2(),
            'base_subtotal_incl_tax' => $purchaseOrder->getTaxedTotal(),
            'subtotal_incl_tax' => $purchaseOrder->getTaxedTotal(),
            'base_currency_code' => $this->getStore()->getBaseCurrencyCode(),
            'email' => $purchaseOrder->getEmailAddress(),
            'company' => $purchaseOrder->getCompanyName(),
            'contact_firstname' => $purchaseOrder->getContactFirstName(),
            'contact_lastname' => $purchaseOrder->getContactLastName(),
            'contact_middlename' => null,
            'contact_prefix' => null,
            'contact_suffix' => null,
            'street' => implode("\n", array_filter([$purchaseOrder->getAddress1(), $purchaseOrder->getAddress2(), $purchaseOrder->getAddress3()])),
            'city' => $purchaseOrder->getCity(),
            'region' => $regionName,
            'region_id' => $regionId,
            'postcode' => $purchaseOrder->getZip(),
            'country_id' => $purchaseOrder->getCountry() ? $purchaseOrder->getCountry()->getIsoCountry() : null,
            'telephone' => $purchaseOrder->getPhoneNumber(),
            'po_number' => $purchaseOrder->getPoNumber(),
            'global_currency_code' => $this->getStore()->getBaseCurrencyCode(),
            'order_currency_code' => $purchaseOrder->getAltCurrencyCode(),
            'shipping_method' => $shippingMethod,
            'store_currency_code' => $this->getStore()->getBaseCurrencyCode(),
            'store_name' => $this->getStore()->getName(),
            'created_at' => $this->getTimestamp($purchaseOrder->getDateEntered(), null)
        ]);

        /** @var Mage_Core_Model_Resource $resource */
        
        $resource = $objectManager->create('\Magento\Framework\App\ResourceConnection');
        $connection = $resource->getConnection();
        $orderTable = $resource->getTableName('sales_order');
        $orderItemTable = $resource->getTableName('sales_order_item');

        $totalWeight = 0;

        foreach ($purchaseOrder->getLines() as $line) {
            if ($line->getJobId()) {
                $orderId = $connection->fetchOne($connection->select()->from($orderTable, 'entity_id')->where('epace_job_id = ?', $line->getJobId()));
                if (!$orderId) {
                    throw new \Exception('Unable to find order with job ' . $line->getJobId() . ' for purchase order line ' . $line->getId());
                }
            } else {
                $orderId = null;
            }
            if ($line->getJobPartNumber()) {
                $orderItemId = $connection->fetchOne($connection->select()->from($orderItemTable, 'item_id')->where('order_id = ?', $orderId)->where('epace_job_part = ?', $line->getJobPartNumber()));
                if (!$orderItemId) {
                    throw new \Exception('Unable to find order item ' . $line->getJobPartKey() . ' for purchase order line ' . $line->getId());
                }
            } else {
                $orderItemId = null;
            }

            $taxPercent = 0;
            if ($line->getSalesTaxRate1()) {
                $taxPercent = $line->getSalesTaxRate1()->getTaxRate();
            }
            if ($line->getSalesTaxRate2()) {
                $taxPercent += $line->getSalesTaxRate2()->getTaxRate();
            }
            $item = $objectManager->create('Blackbox\EpaceImport\Model\PurchaseOrder\Item');
            $item->setData([
                'epace_purchase_order_line_id' => $line->getId(),
                'order_id' => $orderId,
                'store_id' => $this->getStore()->getId(),
                'created_at' => $this->getTimestamp($line->getDateEntered(), null),
                'order_item_id' => $orderItemId,
                'weight' => (float)$line->getTotalWeight(),
                'description' => $line->getDescription(),
                'qty' => $line->getQtyOrdered(),
                'price' => $line->getUnitPrice(),
                'extended_price' => $line->getExtendedPrice(),
                'base_price' => $line->getUnitPrice(),
                'base_extended_price' => $line->getExtendedPrice(),
                'tax_percent' => $taxPercent,
                'uom' => $line->getUom(),
                'qty_uom' => $line->getQtyUom(),
                'row_weight' => $line->getTotalWeight()
            ]);
            $mpo->addItem($item);

            $totalWeight += (float)$line->getTotalWeight();
        }

        $mpo->setWeight($totalWeight);

        if ($shipToContact = $purchaseOrder->getShipToContact()) {
            $shipToAddress = $objectManager->create('Blackbox\EpaceImport\Model\Address')->load($shipToContact->getId(), 'epace_contact_id');
            if (!$shipToAddress->getId()) {
                $this->importEpaceAddress($shipToContact, $shipToAddress);
                $shipToAddress->save();
            }
            $mpo->setShipToAddress($shipToAddress);
        }

        if ($vendorContact = $purchaseOrder->getVendorContact()) {
            $vendorAddress = $objectManager->create('Blackbox\EpaceImport\Model\Address')->load($vendorContact->getId(), 'epace_contact_id');
            if (!$vendorAddress->getId()) {
                $this->importEpaceAddress($vendorContact, $vendorAddress);
                $vendorAddress->save();
            }
            $mpo->setVendorAddress($vendorAddress);
        }

        if ($purchaseOrder->getVendor()) {
            $mVendor = $objectManager->create('Blackbox\EpaceImport\Model\Vendor')->load($purchaseOrder->getVendor()->getId(), 'epace_vendor_id');
            if (!$mVendor->getId()) {
                $this->importVendor($purchaseOrder->getVendor(), $mVendor);
                $mVendor->save();
            }
            $mpo->setVendorId($mVendor->getId());
        }

        return $mpo;
    }

    /**
     * @param \Blackbox\Epace\Model\Epace\Contact $contact
     * @param Blackbox_EpaceImport_Model_Address $address
     * @return Blackbox_EpaceImport_Model_Address
     */
    public function importEpaceAddress(\Blackbox\Epace\Model\Epace\Contact $contact, Blackbox_EpaceImport_Model_Address $address = null)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        if (!$address) {
            $address = $objectManager->create('Blackbox\EpaceImport\Model\Address');
        }
        $customerId = null;
        if ($contact->getCustomer()) {
            $customer = $this->getCustomerFromCustomer($contact->getCustomer());
            $customerId = $customer->getId();
        }

        $salesPersonId = null;
        if ($contact->getSalesPerson()) {
            $salesPersonCustomer = $this->getCustomerFromSalesPerson($contact->getSalesPerson());
            $salesPersonId = $salesPersonCustomer->getId();
        }

        /** @var Mage_Directory_Model_Resource_Region_Collection $regionCollection */
        $regionCollection = $objectManager->create('Magento\Directory\Model\ResourceModel\Region\Collection');
        $regionCollection->addFieldToFilter('country_id', $contact->getCountry()->getIsoCountry())
            ->addFieldToFilter('code', $contact->getState());
        if ($region = $regionCollection->getFirstItem()) {
            $regionName = $region->getDefaultName();
            $regionId = $region->getId();
        } else {
            $regionName = $contact->getState();
            $regionId = null;
        }

        $address->setData([
            'region_id' => $regionId,
            'customer_id' => $customerId,
            'sales_person_id' => $salesPersonId,
            'epace_contact_id' => $contact->getId(),
            'fax' => $this->getFaxFromContact($contact),
            'region' => $regionName,
            'postcode' => $contact->getZip(),
            'lastname' => $contact->getLastName(),
            'street' => implode("\n", array_filter([$contact->getAddress1(), $contact->getAddress2(), $contact->getAddress3()])),
            'city' => $contact->getCity(),
            'email' => $contact->getEmail(),
            'telephone' => $this->getPhoneFromContact($contact),
            'country_id' => $contact->getCountry()->getIsoCountry(),
            'firstname' => $contact->getFirstName(),
            'company' => $contact->getCompanyName()
        ]);
        return $address;
    }

    /**
     * @param \Blackbox\Epace\Model\Epace\Vendor $vendor
     * @param Blackbox_EpaceImport_Model_Vendor $mVendor
     * @return Blackbox_EpaceImport_Model_Vendor
     */
    public function importVendor(\Blackbox\Epace\Model\Epace\Vendor $vendor, \Blackbox\EpaceImport\Model\Vendor $mVendor = null)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        if (!$mVendor) {
            $mVendor = $objectManager->create('Blackbox\EpaceImport\Model\Vendor');
        }

        if ($vendor->getShipVia()) {
            $shippingMethod = $this->getShippingMethod($vendor->getShipVia());
        } else {
            $shippingMethod = null;
        }

        $shipToAddressId = null;
        $paymentAddressId = null;
        $shipFromAddressId = null;

        if ($vendor->getShipToContactId()) {
            $shipToAddress = $objectManager->create('Blackbox\EpaceImport\Model\Address')->load($vendor->getShipToContactId(), 'epace_contact_id');
            if (!$shipToAddress->getId() && $vendor->getShipToContact()) {
                $this->importEpaceAddress($vendor->getShipToContact(), $shipToAddress);
                $shipToAddress->save();
            }

            $shipToAddressId = $shipToAddress->getId();
        }

        if ($vendor->getPaymentContactId()) {
            
            $paymentAddress = $objectManager->create('Blackbox\EpaceImport\Model\Address')->load($vendor->getPaymentContactId(), 'epace_contact_id');
            if (!$paymentAddress->getId() && $vendor->getPaymentContact()) {
                $this->importEpaceAddress($vendor->getPaymentContact(), $paymentAddress);
                $paymentAddress->save();
            }

            $paymentAddressId = $paymentAddress->getId();
        }

        if ($vendor->getShipFromContactId()) {
            $shipFromAddress = $objectManager->create('Blackbox\EpaceImport\Model\Address')->load($vendor->getShipFromContactId(), 'epace_contact_id');
            if (!$shipFromAddress->getId() && $vendor->getShipFromContact()) {
                $this->importEpaceAddress($vendor->getShipFromContact(), $shipFromAddress);
                $shipFromAddress->save();
            }

            $shipFromAddressId = $shipFromAddress->getId();
        }

        $mVendor->setData([
            'epace_vendor_id' => $vendor->getId(),
            'type' => $vendor->getVendorType(),
            'website_id' => $this->getWebsiteId(),
            'email' => $vendor->getEmailAddress(),
            'store_id' => $this->getStore()->getId(),
            'created_at' => $this->getTimestamp($vendor->getSetupDate(), null),
            'is_active' => $vendor->getActive(),
            'firstname' => $vendor->getContactFirstName(),
            'lastname' => $vendor->getContactLastName(),
            'middlename' => '',
            'telephone' => $vendor->getPhoneNumber(),
            'shipping_method' => $shippingMethod ? $shippingMethod->getCarrier() . '_' . $shippingMethod->getMethod() : '',
            'ship_to_address_id' => $shipToAddressId,
            'payment_address_id' => $paymentAddressId,
            'ship_from_address_id' => $shipFromAddressId
        ]);

        return $mVendor;
    }

    protected function getPhoneFromContact(\Blackbox\Epace\Model\Epace\Contact $contact)
    {
        if ($contact->getBusinessPhoneNumber()) {
            return $contact->getBusinessPhoneNumber() . ($contact->getBusinessPhoneExtension() ? ', Ext. ' . $contact->getBusinessPhoneExtension() : '');
        }

        if ($contact->getMobilePhoneNumber()) {
            return $contact->getMobilePhoneNumber();
        }

        if ($contact->getHomePhoneNumber()) {
            return $contact->getHomePhoneNumber();
        }

        if ($contact->getOtherPhoneNumber()) {
            return $contact->getOtherPhoneNumber();
        }

        return '';
    }

    protected function getFaxFromContact(\Blackbox\Epace\Model\Epace\Contact $contact)
    {
        if ($contact->getBusinessFaxNumber()) {
            return $contact->getBusinessFaxNumber() . ($contact->getBusinessFaxExtension() ? ', Ext. ' . $contact->getBusinessFaxExtension() : '');
        }

        if ($contact->getHomeFaxNumber()) {
            return $contact->getHomeFaxNumber();
        }

        return '';
    }

    /**
     * @param Mage_Shipping_Model_Carrier_Abstract $carrier
     * @param $method
     * @param $weight
     * @param \Magento\Sales\Model\Order\Address $origAddress
     * @param \Magento\Sales\Model\Order\Address $destAddress
     * @return bool|array
     */
    public function getShippingPrice(\Magento\Shipping\Model\Carrier\AbstractCarrier $carrier, $method, $weight, \Magento\Sales\Model\Order\Address $origAddress, \Magento\Sales\Model\Order\Address $destAddress)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        if ($carrier instanceof \Magento\Ups\Model\Carrier || $carrier instanceof \Magento\Fedex\Model\Carrier) {
            /** @var Mage_Shipping_Model_Rate_Request $request */
            $request = $objectManager->create('\Magento\Quote\Model\Quote\Address\RateRequest');

            $request->setOrigCountryId($origAddress->getCountryId());
            $request->setOrigRegionId($origAddress->getRegionId());
            $request->setOrigCity($origAddress->getCity());
            $request->setOrigPostcode($origAddress->getPostcode());

            $request->setLimitMethod($method);

            $request->setDestCountryId($destAddress->getCountryId());
            $request->setDestRegionId($destAddress->getRegionId());
            $request->setDestRegionCode($destAddress->getRegionCode());
            $request->setDestCity($destAddress->getCity());
            $request->setDestPostcode($destAddress->getPostcode());
            $request->setDestStreet($destAddress->getStreetFull());

            $request->setPackageWeight($weight);
            $request->setBaseCurrency($this->getStore()->getBaseCurrency());

            $result = $carrier->collectRates($request);
            if (!$result->getError()) {
                foreach ($result->getAllRates() as $rate) {
                    if ($rate->getMethod() == $method) {
                        return [
                            'price' => $rate->getPrice(),
                            'cost' => $rate->getCost()
                        ];
                    }
                }
            } else {
                $msg = null;
                foreach ($result->getAllRates() as $rate) {
                    if ($rate->getErrorMessage()) {
                        $msg = $rate->getErrorMessage();
                        break;
                    }
                }
                if (!$msg) {
                    $msg = 'Unable to load carrier rates.';
                }
                throw new \Exception($msg);
            }
        }

        return false;
    }

    /**
     * @param \Magento\Sales\Model\Order\Shipment $shipment
     * @param \Magento\Sales\Model\Order\Address|null $origAddress
     * @param \Magento\Sales\Model\Order\Address|null $destAddress
     * @return array|bool
     */
    public function getShipmentPrice(\Magento\Sales\Model\Order\Shipment $shipment, \Magento\Sales\Model\Order\Address $origAddress = null, \Magento\Sales\Model\Order\Address $destAddress = null)
    {
        if ($shipment->getCarrier() != 'ups') {
            return false;
        }

        $carrier = $this->getCarrier($shipment->getCarrier());
        if (!$carrier instanceof \Magento\Shipping\Model\Carrier\AbstractCarrier) {
            return false;
        }

        if (!$origAddress) {
            $order = $shipment->getOrder();
            $origAddressEpaceJobContactId = $order->getEpaceShipToJobContact();

            foreach ($order->getAddressesCollection() as $address) {
                if ($address->getEpaceJobContactId() == $origAddressEpaceJobContactId) {
                    $origAddress = $address;
                    break;
                }
            }

            if (!$origAddress) {
                return false;
            }
        }

        if (!$destAddress) {
            $order = $shipment->getOrder();
            $destAddressId = $shipment->getShippingAddressId();
            foreach ($order->getAddressesCollection() as $address) {
                if ($address->getId() == $destAddressId) {
                    $destAddress = $address;
                    break;
                }
            }
        }

        if (!$destAddress) {
            return false;
        }

        return $this->getShippingPrice($carrier, $shipment->getMethod(), $shipment->getTotalWeight(), $origAddress, $destAddress);
    }

    public function calculatePartsPriceFromEstimate(\Blackbox\Epace\Model\Epace\Job $job)
    {
        if (!$job->isSourceEstimate()) {
            throw new \Exception('Job source is not estimate');
        }
        $amountToInvoice = 0;
        foreach ($job->getParts() as $part) {
            $estimatePart = $part->getEstimatePart();
            if (!$estimatePart) {
                throw new \Exception('Unable to find estimate part for job part ' . $part->getId());
            }

            $quantity = null;
            foreach ($estimatePart->getQuantities() as $_quantity) {
                if ($_quantity->getQuantityOrdered() == $part->getQtyOrdered()) {
                    $quantity = $_quantity;
                    break;
                }
            }

            if (!$quantity) {
                throw new \Exception('Unable to find estimate quantity with qty ordered ' . $part->getQtyOrdered() . ' in estimate part ' . $estimatePart->getId() . ' for job part ' . $part->getId());
            }

            $amountToInvoice += (float)$quantity->getPrice();
        }

        return $amountToInvoice;
    }

    public function calculatePartsPrice(\Blackbox\Epace\Model\Epace\Job $job)
    {
        $amountToInvoice = 0;
        foreach ($job->getParts() as $part) {
            $amountToInvoice += (float)$part->getQuotedPrice();
        }
        return $amountToInvoice;
    }

    /**
     * @return Mage_Catalog_Model_Product
     */
    public function getProduct()
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        if (!$this->product) {
            $this->product = $objectManager->create('\Magento\Catalog\Model\Product');
            $id = $this->product->getIdBySku('epace_blank');
            if ($id) {
                $this->product->load($id);
            } else {
                $this->product->setData([
                    'website_ids' => [1],
                    'sku' => 'epace_blank',
                    'name' => 'Epace Blank',
                    'attribute_set_id' => 4,
                    'created_at' => time(),
                    'status' => 0,
                    'tax_class_id' => 4,
                    'visibility' => \Magento\Catalog\Model\Product\Visibility::VISIBILITY_NOT_VISIBLE
                ])->save();
            }
        }

        return $this->product;
    }

    /**
     * @param Mage_Sales_Model_Order $order
     * @param string $jobStatus
     */
    protected function setOrderStatus(\Magento\Sales\Model\Order $order, $jobStatus)
    {
        $statuses = [
            \Blackbox\Epace\Model\Epace\Job_Status::STATUS_AUTO_BILLING_OK => 'processing',
            \Blackbox\Epace\Model\Epace\Job_Status::STATUS_CLOSED => 'closed',
            \Blackbox\Epace\Model\Epace\Job_Status::STATUS_COST_TRANSFER => 'processing',
            \Blackbox\Epace\Model\Epace\Job_Status::STATUS_CREDIT_HOLD => 'holded',
            \Blackbox\Epace\Model\Epace\Job_Status::STATUS_JOB_CANCELLED => 'canceled',
            \Blackbox\Epace\Model\Epace\Job_Status::STATUS_OPEN => ['state' => 'new', 'status' => 'pending'],
            \Blackbox\Epace\Model\Epace\Job_Status::STATUS_PARTL_BILL => 'pending',
            \Blackbox\Epace\Model\Epace\Job_Status::STATUS_SEND_TO_PRINERGY => 'processing',
            \Blackbox\Epace\Model\Epace\Job_Status::STATUS_SHIPPED => 'complete',
            \Blackbox\Epace\Model\Epace\Job_Status::STATUS_TO_PLANTMANAGER => 'processing'
        ];
        $status = $statuses[$jobStatus];
        if (!$status) {
            $status = [
                'state' => 'new',
                'status' => 'pending'
            ];
        }
        if (is_array($status)) {
            $order->setData('state', $status['state'])->setData('status', $status['status']);
        } else {
            $order->setData('state', $status)->setData('status', $status);
        }
    }

    protected function addAddressToOrder(\Magento\Sales\Model\Order $order, \Blackbox\Epace\Model\Epace\Contact $contact, $type, \Blackbox\Epace\Model\Epace\Job\Contact $jobContact)
    {
        /** @var Mage_Directory_Model_Resource_Region_Collection $regionCollection */
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $regionCollection = $objectManager->create('Magento\Directory\Model\ResourceModel\Region\Collection');
        $regionCollection->addFieldToFilter('country_id', $contact->getCountry()->getIsoCountry())
            ->addFieldToFilter('code', $contact->getState());
        if ($region = $regionCollection->getFirstItem()) {
            $regionName = $region->getDefaultName();
            $regionId = $region->getId();
        } else {
            $regionName = $contact->getState();
            $regionId = null;
        }

        if ($contact->getCustomer()) {
            $customerId = $this->getCustomerFromCustomer($contact->getCustomer())->getId();
        } else {
            $customerId = $order->getCustomerId();
        }

        if ($contact->getSalesPerson()) {
            $salesPersonCustomerId = $this->getCustomerFromSalesPerson($contact->getSalesPerson())->getId();
        } else {
            $salesPersonCustomerId = $order->getSalesPersonId();
        }
        $address = $objectManager->create('\Magento\Sales\Model\Order\Address');
        $address->addData([
            'customer_id' => $customerId,
            'sales_person_id' => $salesPersonCustomerId,
            'address_type' => $type,
            'fax' => '',
            'region_id' => $regionId,
            'region' => $regionName,
            'postcode' => $contact->getZip(),
            'lastname' => $contact->getLastName(),
            'street' => $contact->getAddress1(),
            'city' => $contact->getCity(),
            'email' => $contact->getEmail(),
            'telephone' => $contact->getMobilePhoneNumber() ?: $contact->getBusinessPhoneExtension() . ' ' . $contact->getBusinessPhoneNumber(),
            'country_id' => $contact->getCountry()->getIsoCountry(),
            'firstname' => $contact->getFirstName(),
            'company' => $contact->getCompanyName(),
            'epace_job_contact_id' => $jobContact->getId(),
            'epace_contact_id' => $contact->getId()
        ]);
        $order->addAddress($address);

        return $address;
    }

    public function getCustomerFromCustomer(\Blackbox\Epace\Model\Epace\Customer $customer)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        if (isset($this->customerCustomerMap[$customer->getId()])) {
            return $this->customerCustomerMap[$customer->getId()];
        }

        $email = $customer->getEmail();
        if (!$email) {
            $email = 'customer' . $customer->getId() . 'epace@socalgraph.com';
        }

        /** @var Mage_Customer_Model_Customer $magentoCustomer */
        $magentoCustomer = $objectManager->create('\Magento\Customer\Model\Customer')
                                ->setWebsiteId($this->getWebsiteId())->loadByEmail($email);
        if (!$magentoCustomer->getId()) {
            $magentoCustomer
                ->setWebsiteId($this->getWebsiteId())
                ->setStore($this->getStore())
                ->setFirstname($customer->getCustName())
                ->setLastname('')
                ->setEmail($email)
                ->setPassword('password');

            /** @var Mage_Directory_Model_Resource_Region_Collection $regionCollection */
            $regionCollection = $objectManager->create('Magento\Directory\Model\ResourceModel\Region\Collection');
            $regionCollection->addFieldToFilter('country_id', $customer->getCountry()->getIsoCountry())
                ->addFieldToFilter('code', $customer->getState());
            if ($region = $regionCollection->getFirstItem()) {
                $regionName = $region->getDefaultName();
            } else {
                $regionName = $customer->getState();
            }

            $magentoCustomer->save();
            $address = $objectManager->create('\Magento\Customer\Model\Address')->setData([
                'parent_id' => $magentoCustomer->getId(),
                'is_active' => 1,
                'firstname' => $customer->getContactFirstName(),
                'lastname' => $customer->getContactLastName(),
                'company' => $customer->getCustName(),
                'street' => implode(PHP_EOL, array_filter([
                    $customer->getAddress1(),
                    $customer->getAddress2(),
                    $customer->getAddress3()
                ])),
                'city' => $customer->getCity(),
                'country_id' =>  $customer->getCountry()->getIsoCountry(),
                'region' => $regionName,
                'region_id' => $region ? $region->getId() : null,
                'postcode' => $customer->getZip(),
                'telephone' => $customer->getPhoneNumber(),
                'fax' => ''
            ])->save();
            $magentoCustomer->addAddress($address);

            $this->writeln('Created customer ' . $magentoCustomer->getId() . ' from epace Customer ' . $customer->getId());
        }

        return $this->customerCustomerMap[$customer->getId()] = $magentoCustomer;
    }

    public function getCustomerFromSalesPerson(\Blackbox\Epace\Model\Epace\SalesPerson $salesPerson)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        if (isset($this->salesPersonCustomerMap[$salesPerson->getId()])) {
            return $this->salesPersonCustomerMap[$salesPerson->getId()];
        }

        $email = $salesPerson->getEmail();
        if (!$email) {
            $email = 'salesPerson' . $salesPerson->getId() .'epace@socalgraph.com';
        }
        $customer = $objectManager->create('\Magento\Customer\Model\Customer')
                    ->setWebsiteId($this->getWebsiteId())->loadByEmail($email);
        if (!$customer->getId()) {
            $name = explode(' ', $salesPerson->getName(), 2);
            $customer
                ->setWebsiteId($this->getWebsiteId())
                ->setStore($this->getStore())
                ->setFirstname($name[0])
                ->setLastname($name[1])
                ->setEmail($email)
                ->setPassword('password')
                ->setGroupId($this->getWholesaleCustomerGroupId());
            $customer->save();
            $this->writeln('Created customer ' . $customer->getId() . ' from SalesPerson ' . $salesPerson->getId());
        }

        return $this->salesPersonCustomerMap[$salesPerson->getId()] = $customer;
    }

    public function getCustomerFromCSR(\Blackbox\Epace\Model\Epace\CSR $csr)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        if (isset($this->csrCustomerMap[$csr->getId()])) {
            return $this->csrCustomerMap[$csr->getId()];
        }

        $email = $csr->getEmail();
        if (!$email) {
            $email = 'csr' . $csr->getId() .'epace@socalgraph.com';
        }

        $customer = $objectManager->create('\Magento\Customer\Model\Customer')->setWebsiteId($this->getWebsiteId())->loadByEmail($email);
        if (!$customer->getId()) {
            $name = explode(' ', $csr->getName(), 2);
            $customer
                ->setWebsiteId($this->getWebsiteId())
                ->setStore($this->getStore())
                ->setFirstname($name[0])
                ->setLastname($name[1])
                ->setEmail($email)
                ->setPassword('password')
                ->setGroupId($this->getCSRCustomerGroupId());
            $customer->save();
            $this->writeln('Created customer ' . $customer->getId() . ' from CSR ' . $csr->getId());
        }

        return $this->csrCustomerMap[$csr->getId()] = $customer;
    }

    public function getWholesaleCustomerGroupId()
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        if (is_null($this->wholesaleGroupId)) {
            $groupCollection = $objectManager->create('\Magento\Customer\Model\ResourceModel\Group\Collection')
                ->addFieldToFilter('customer_group_code', ['like' => 'wholesale']);
            /** @var Mage_Customer_Model_Group $group */
            $group = $groupCollection->getFirstItem();
            if (!$group->getId()) {
                $group->setCode('Wholesale')->setTaxClassId(3)->save();
            }
            $this->wholesaleGroupId = $group->getId();
        }
        return $this->wholesaleGroupId;
    }

    public function getCSRCustomerGroupId()
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        if (is_null($this->csrGroupId)) {
            $groupCollection = $objectManager->create('\Magento\Customer\Model\ResourceModel\Group\Collection')
                ->addFieldToFilter('customer_group_code', 'CSR');
            /** @var Mage_Customer_Model_Group $group */
            $group = $groupCollection->getFirstItem();
            if (!$group->getId()) {
                $group->setCode('CSR')->setTaxClassId(3)->save();
            }
            $this->csrGroupId = $group->getId();
        }
        return $this->csrGroupId;
    }

    /**
     * @param \Blackbox\Epace\Model\Epace\Ship_Via $shipVia
     * @return Mage_Shipping_Model_Rate_Result_Method
     */
    public function getShippingMethod(\Blackbox\Epace\Model\Epace\Ship\Via $shipVia)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        if (is_null($this->carriers)) {
            $this->_loadCarriers();
        }

        /** @var Blackbox_EpaceImport_Helper_Data $helper */

        $code = $this->getShipViaMethodCode($shipVia);
        /** @var Blackbox_EpaceImport_Model_Shipping_Carrier $epaceCarrier */
        $epaceCarrier = null;

        foreach ($this->carriers as $carrier) {
            if ($carrier instanceof \Blackbox\EpaceImport\Model\Shipping\Carrier) {
                $epaceCarrier = $carrier;
                continue;
            }
            if ($carrier instanceof \Magento\Fedex\Model\Carrier) {
                $methods = $carrier->getCode('method');
            } else {
                $methods = $carrier->getAllowedMethods();
            }
            foreach ($methods as $method => $title) {
                if (strtolower($carrier->getCarrierCode() . '_' . $method) == $code) {
                    return $objectManager->create('\Magento\Shipping\Model\Rate\Result')
                        ->setData([
                        'carrier' => $carrier->getCarrierCode(),
                        'method' => $method,
                        'carrier_title' => $carrier->getConfigData('title'),
                        'method_title' => $title
                    ]);
                }
            }
        }

        if ($epaceCarrier) {
            $carrier = $epaceCarrier;
        } else {
            $carrier = $objectManager->create('Blackbox\EpaceImport\Model\Shipping\Carrier');
        }

        foreach ($carrier->getAllowedMethods() as $method => $title) {
            if ($method == $code) {
                return $objectManager->create('\Magento\Shipping\Model\Rate\Result')
                    ->setData([
                    'carrier' => $carrier->getCarrierCode(),
                    'method' => $method,
                    'carrier_title' => $carrier->getConfigData('title'),
                    'method_title' => $title
                ]);
            }
        }

        return $carrier->getEmptyRate();
    }

    public function getCarrier($code)
    {
        if (is_null($this->carriers)) {
            $this->_loadCarriers();
        }

        foreach ($this->carriers as $carrier) {
            if ($carrier->getCarrierCode() == $code) {
                return $carrier;
            }
        }

        return null;
    }

    protected function _loadCarriers()
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $this->carriers = $objectManager->create('Magento\Shipping\Model\Config')->getActiveCarriers();

        $fedexFound = false;
        $upsFound = false;
        $found = 0;
        foreach ($this->carriers as $carrier) {
            if ($carrier instanceof \Magento\Fedex\Model\Carrier) {
                $fedexFound = true;
                if (++$found == 2) {
                    break;
                }
            } else if ($carrier instanceof \Magento\Ups\Model\Carrier) {
                $upsFound = true;
                if (++$found == 2) {
                    break;
                }
            }
        }

        if (!$fedexFound) {
            
            $this->carriers[] = $objectManager->create('\Magento\Fedex\Model\Carrier');
        }
        if (!$upsFound) {
            $this->carriers[] = $objectManager->create('\Magento\Ups\Model\Carrier');
        }
    }

    public function getWebsiteId()
    {
        return 1;
    }

    public function getStore()
    {
        /** 
         * It is implemented as Magento 1 standards
         */
        return Mage::app()->getStore(1);
    }

    public function getSalesRepsOptions()
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        if (is_null($this->salesRepsOptions)) {
            $this->salesRepsOptions = [];
            $wholesaleGroup = $objectManager->create('\Magento\Customer\Model\ResourceModel\Group\Collection')
                                ->addFieldToFilter('customer_group_code', ['like' => 'wholesale'])
                                ->getFirstItem();

            /** @var Mage_Customer_Model_Resource_Customer_Collection $collection */
            
            $collection = $objectManager->create('\Magento\Customer\Model\ResourceModel\Customer\Collection');
            $collection
                ->addAttributeToSelect('prefix')
                ->addAttributeToSelect('firstname')
                ->addAttributeToSelect('middlename')
                ->addAttributeToSelect('lastname')
                ->addAttributeToSelect('suffix')
                ->addFieldToFilter('group_id', $wholesaleGroup->getId());
            /** @var Mage_Customer_Model_Customer $customer */
            foreach ($collection->getItems() as $customer) {
                $this->salesRepsOptions[$customer->getId()] = $customer->getName();
            }
        }

        return $this->salesRepsOptions;
    }

    protected function writeln($message)
    {
        if (is_callable($this->output)) {
            call_user_func($this->output, $message);
        }
    }

    public function getDateTime($dateString, $timeString)
    {
        $dateTime = new \DateTime($dateString, new \DateTimeZone('UTC'));

        if (empty($timeString) || $dateString == $timeString) {
            return $dateTime;
        }

        $timeDateTime = new \DateTime($timeString, new \DateTimeZone('UTC'));
        if ($timeDateTime->getTimestamp() > 3600 * 24 * 10) {
            return $dateTime;
        }

        $dateTime->setTime(0, 0, 0);
        $dateTime->setTimestamp($dateTime->getTimestamp() + $timeDateTime->getTimestamp());
        return $dateTime;
    }

    public function getTimestamp($dateString, $timeString)
    {
        return $this->getDateTime($dateString, $timeString)->getTimestamp();
    }

    public function convertOutputDateWithoutDST($date)
    {
        if ($date instanceof \DateTime) {
            $dateTime = $date;
        } else {
            $dateTime = new \DateTime($date, new DateTimeZone('UTC'));
        }
        $dateTime->setTimezone(new \DateTimeZone($this->scopeConfig->getValue('general/locale/timezone', \Magento\Store\Model\ScopeInterface::SCOPE_STORE)));
        if ($dateTime->format('I')) {
            $dateTime->setTimestamp($dateTime->getTimestamp() - 3600);
        }
        return $dateTime;
    }

    public function updateEstimate(\Blackbox\EpaceImport\Model\Estimate $estimate, $logChanges = false)
    {
        /** @var \Blackbox\Epace\Model\Epace\Estimate $epaceEstimate */
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $epaceEstimate = $objectManager->create('Blackbox\EpaceImport\Model\Estimate')
                                ->load($estimate->getEpaceEstimateId());
        if (!$epaceEstimate->getId()) {
            return;
        }

        $newEstimate = $this->importEstimate($epaceEstimate);
        $changes = $this->updateObject($estimate, $newEstimate, [
            'store_name',
//            'created_at',
            'updated_at'
        ]);
        if ($logChanges) {
            $this->logChanges('Estimate ' . $estimate->getId() . ' updates', $changes);
        }

        $oldItems = $estimate->getAllItems();
        foreach ($oldItems as $item) {
            $item->setDataChanges(false);
        }

        foreach ($newEstimate->getAllItems() as $newItem) {
            $oldItem = null;
            foreach ($oldItems as $key => $_oldItem) {
                if ($_oldItem->getEpaceEstimateQuantityId() == $newItem->getEpaceEstimateQuantityId()) {
                    $oldItem = $_oldItem;
                    unset($oldItems[$key]);
                    break;
                }
            }

            if ($oldItem) {
                $changes = $this->updateObject($oldItem, $newItem, [
                    'estimate_id',
                    'store_id'
                ]);
                $this->logChanges('Estimate item ' . $oldItem->getId() . ' updates', $changes);
                $oldItem->save();
            } else {
                $this->writeln('Added estimate item ' . print_r($newItem->getData(), true));
                $estimate->addItem($newItem);
                $newItem->save();
            }
        }

        // delete not found old items
        foreach ($oldItems as $deletedItem) {
            $deletedItem->isDeleted(true);
            $this->writeln('Deleted item ' . $deletedItem->getId() . ', epace estimate part id: ' . $deletedItem->getEpaceEstimatePartId() . ', epace estimate quantity id: ' . $deletedItem->getEpaceEstimateQuantityId());
        }

        $oldStatusHistories = $estimate->getAllStatusHistory();
        foreach ($newEstimate->getAllStatusHistory() as $statusHistory) {
            foreach ($oldStatusHistories as $oldStatusHistory) {
                if ($statusHistory->getComment() == $oldStatusHistory->getComment()) {
                    continue 2;
                }
            }

            $estimate->addStatusHistory($statusHistory);
            $statusHistory->save();
        }

        $estimate->save();
    }

    public function updateOrder(\Magento\Sales\Model\Order $order, $logChanges = false)
    {
        /** @var \Blackbox\Epace\Model\Epace\Job $job */
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $job = $objectManager->create('Blackbox\EpaceImport\Model\Job')->load($order->getEpaceJobId());
        if (!$job->getId()) {
            return;
        }

        $ignoreFields = [
            'entity_id',
            'billing_address_id',
            'shipping_address_id',
            'created_at',
            'updated_at',
            'store_name',
        ];

        $newOrder = $this->importJob($job);
        $changes = $this->updateObject($order, $newOrder, $ignoreFields);
        if ($logChanges) {
            $this->logChanges('Order ' . $order->getId() . ' updates', $changes);
        }

        $ignoreFields = [
            'entity_id',
            'order_id',
//            'created_at',
            'updated_at'
        ];

        /** @var \Magento\Sales\Model\Order\Item[] $oldItems */
        $oldItems = $order->getAllItems();
        foreach ($oldItems as $item) {
            $item->setDataChanges(false);
        }

        foreach ($newOrder->getAllItems() as $newItem) {
            $oldItem = null;
            foreach ($oldItems as $key => $_oldItem) {
                if ($_oldItem->getEpaceJobPart() == $newItem->getEpaceJobPart()) {
                    $oldItem = $_oldItem;
                    unset($oldItems[$key]);
                    break;
                }
            }

            if ($oldItem) {
                $changes = $this->updateObject($oldItem, $newItem, $ignoreFields);
                $this->logChanges('Order item ' . $oldItem->getId() . ' updates', $changes);
                $oldItem->save();
            } else {
                $this->writeln('Added new order item ' . print_r($newItem->getData(), true));
                $order->addItem($newItem);
                $newItem->save();
            }
        }

        foreach ($oldItems as $deletedItem) {
            $this->writeln('Deleted order item ' . $deletedItem->getId() . ', epace job part: ' . $order->getEpaceJobId() . ':' . $deletedItem->getEpaceJobPart());
            $deletedItem->isDeleted(true);
        }

        $ignoreFields = [
            'entity_id',
            'parent_id'
        ];

        /** @var \Magento\Sales\Model\Order\Address[] $oldAddresses */
        $oldAddresses = $order->getAddressesCollection()->getItems();
        foreach ($oldAddresses as $address) {
            $address->setDataChanges(false);
        }

        foreach ($newOrder->getAddressesCollection() as $newAddress) {
            $oldAddress = null;
            foreach ($oldAddresses as $key => $_oldAddress) {
                if ($_oldAddress->getEpaceJobContactId() == $newAddress->getEpaceJobContactId() && $_oldAddress->getAddressType() == $newAddress->getAddressType()) {
                    $oldAddress = $_oldAddress;
                    unset($oldAddresses[$key]);
                    break;
                }
            }

            if ($oldAddress) {
                $changes = $this->updateObject($oldAddress, $newAddress, $ignoreFields);
                $this->logChanges('Order address ' . $oldAddress->getId() . ' updates', $changes);
                $oldAddress->save();
            } else {
                $this->writeln('Added new order address ' . print_r($newAddress->getData(), true));
                $order->addAddress($newAddress);
                $newAddress->save();
            }
        }

        foreach ($oldAddresses as $deletedAddress) {
            $this->writeln('Deleted order address ' . $deletedAddress->getId() . ', epace job contact id: ' . $deletedAddress->getEpaceJobContactId() . ', epace contact id: ' . $deletedAddress->getEpaceContactId());
            $deletedAddress->isDeleted(true);
        }

        $oldStatusHistories = $order->getStatusHistoryCollection()->getItems();

        foreach ($newOrder->getStatusHistoryCollection()->getItems() as $newStatusHistory) {
            foreach ($oldStatusHistories as $oldStatusHistory) {
                if ($oldStatusHistory->getComment() == $newStatusHistory->getComment()) {
                    continue 2;
                }
            }
            $order->addStatusHistoryComment($newStatusHistory->getComment());
        }

        $order->save();
    }

    public function updateInvoice(\Magento\Sales\Model\Order\Invoice $invoice, $logChanges = false)
    {
        /** @var \Blackbox\Epace\Model\Epace\Invoice $epaceInvoice */
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $epaceInvoice = $objectManager->create('Blackbox\EpaceImport\Model\Invoice')->load($invoice->getEpaceInvoiceId());
        if (!$epaceInvoice->getId()) {
            return;
        }

        $newInvoice = $this->importInvoice($epaceInvoice);
        $changes = $this->updateObject($invoice, $newInvoice, [
            'order_id',
//            'created_at',
            'updated_at'
        ]);
        if ($logChanges) {
            $this->logChanges('Invoice ' . $invoice->getId() . ' updates', $changes);
        }

        $ignoreFields = [
            'entity_id',
            'parent_id',
            'order_item_id',
//            'created_at',
            'updated_at'
        ];

        /** @var \Magento\Sales\Model\Order\Invoice_Item[] $oldItems */
        $oldItems = $invoice->getAllItems();
        foreach ($oldItems as $item) {
            $item->setDataChanges(false);
        }

        foreach ($newInvoice->getAllItems() as $newItem) {
            $oldItem = null;
            foreach ($oldItems as $key => $_oldItem) {
                if ($_oldItem->getEpaceInvoiceLineId() == $newItem->getEpaceInvoiceLineId()) {
                    $oldItem = $_oldItem;
                    unset($oldItems[$key]);
                    break;
                }
            }

            if ($oldItem) {
                $changes = $this->updateObject($oldItem, $newItem, $ignoreFields);
                $this->logChanges('Invoice item ' . $oldItem->getId() . ' updates', $changes);
                $oldItem->save();
            } else {
                $this->writeln('Added new invoice item ' . print_r($newItem->getData(), true));
                $invoice->addItem($newItem);
                $newItem->save();
            }
        }

        foreach ($oldItems as $deletedItem) {
            $this->writeln('Deleted invoice item ' . $deletedItem->getId() . ', epace invoice line id: ' . $deletedItem->getEpaceInvoiceLineId());
            $deletedItem->isDeleted(true);
        }

//        if ($epaceInvoice->getReceivable()) {
//            $magentoReceivable = Mage::getModel('epacei/receivable')->load($epaceInvoice->getReceivable()->getId(), 'epace_receivable_id');
//            if ($magentoReceivable->getId()) {
//                $this->updateReceivable($magentoReceivable, $epaceInvoice->getReceivable(), $invoice, $logChanges);
//            } else {
//                $this->importReceivable($epaceInvoice->getReceivable(), $invoice)->save();
//            }
//        }

        $invoice->save();
    }

    public function updateReceivable(\Blackbox\EpaceImport\Model\Receivable $receivable, \Blackbox\Epace\Model\Epace\Receivable $epaceReceivable = null, $logChanges = false)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        if (!$epaceReceivable) {
            $epaceReceivable = $objectManager->create('Blackbox\EpaceImport\Model\Receivable')
                                        ->load($receivable->getEpaceReceivableId());
            if (is_null($epaceReceivable->getId())) {
                return;
            }
        }

        $newReceivable = $this->importReceivable($epaceReceivable);
        $changes = $this->updateObject($receivable, $newReceivable, [
//            'created_at',
            'updated_at',
        ]);
        if ($logChanges) {
            $this->logChanges('Receivable ' . $receivable->getId() . ' updates', $changes);
        }

        $receivable->save();
    }

    public function updateShipment(\Magento\Sales\Model\Order\Shipment $shipment, $logChanges = false)
    {
        /** @var \Blackbox\Epace\Model\Epace\Job_Shipment $epaceShipment */
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $epaceShipment = $objectManager->create('Blackbox\EpaceImport\Model\Job\Shipment')
                                    ->load($shipment->getEpaceShipmentId());
        if (!$epaceShipment->getId()) {
            return;
        }

        $newShipment = $this->importShipment($epaceShipment, $shipment->getOrder());
        $epaceShipment->dispose();
        $changes = $this->updateObject($shipment, $newShipment, [
//            'created_at',
            'updated_at'
        ]);
        if ($logChanges) {
            $this->logChanges('Shipment ' . $shipment->getId() . ' updates', $changes);
        }

        /** @var \Magento\Sales\Model\Order\Shipment_Track[] $oldTracks */
        $oldTracks = $shipment->getAllTracks();
        foreach ($newShipment->getAllTracks() as $track) {
            foreach ($oldTracks as $oldTrack) {
                if ($oldTrack->getNumber() == $track->getNumber()) {
                    continue 2;
                }
            }

            $shipment->addTrack($track);
        }

        $shipment->save();
        $newShipment->getOrder()->reset();
        $newShipment->dispose();
    }

    public function updatePurchaseOrder(Blackbox_EpaceImport_Model_PurchaseOrder $purchaseOrder, $logChanges = false)
    {
        /** @var \Blackbox\Epace\Model\Epace\Purchase_Order $epacePurchaseOrder */
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $epacePurchaseOrder = $objectManager->create('Blackbox\EpaceImport\Model\Purchase\Order')
                                        ->load($purchaseOrder->getEpacePurchaseOrderId());
        if (!$epacePurchaseOrder->getId()) {
            return null;
        }

        $newPurchaseOrder = $this->importPurchaseOrder($epacePurchaseOrder);
        $changes = $this->updateObject($purchaseOrder, $newPurchaseOrder, [
            'store_name',
            'updated_at',
        ]);
        if ($logChanges) {
            $this->logChanges('Purchase Order ' . $purchaseOrder->getId() . ' updates', $changes);
        }

        $ignoreFields = [
            'entity_id',
            'purchase_order_id',
//            'created_at',
            'updated_at'
        ];

        $oldItems = $purchaseOrder->getAllItems();
        foreach ($oldItems as $item) {
            $item->setDataChanges(false);
        }

        foreach ($newPurchaseOrder->getAllItems() as $newItem) {
            $oldItem = null;
            foreach ($oldItems as $key => $_oldItem) {
                if ($_oldItem->getEpacePurchaseOrderLineId() == $newItem->getEpacePurchaseOrderLineId()) {
                    $oldItem = $_oldItem;
                    unset($oldItems[$key]);
                    break;
                }
            }

            if ($oldItem) {
                $changes = $this->updateObject($oldItem, $newItem, $ignoreFields);
                $this->logChanges('Purchase order item ' . $oldItem->getId() . ' updates', $changes);
                $oldItem->save();
            } else {
                $this->writeln('Added new purchase order item ' . print_r($newItem->getData(), true));
                $purchaseOrder->addItem($newItem);
                $newItem->save();
            }
        }

        foreach ($oldItems as $deletedItem) {
            $deletedItem->isDeleted(true);
        }

        $purchaseOrder->save();
    }

    /**
     * @param Mage_Core_Model_Abstract $old
     * @param Mage_Core_Model_Abstract $new
     * @param array $ignoreFields
     * @return array
     */
    public function updateObject($old, $new, $ignoreFields = [])
    {
        $changes = [];
        $fields = $old->getResource()->getReadConnection()->describeTable($old->getResource()->getMainTable());
        foreach ($new->getData() as $key => $value) {
            if (in_array($key, $ignoreFields) || !isset($fields[$key])) {
                continue;
            }
            $oldValue = $old->getData($key);
            if (!is_null($value)) {
                $fieldDescription = $fields[$key];
                if ($fieldDescription) {
                    switch ($fieldDescription['DATA_TYPE']) {
                        case 'decimal':
                            $value = round($value, $fieldDescription['SCALE']);
                            break;
                        case 'timestamp':
                            if (is_string($oldValue) && !is_numeric($oldValue)) {
                                $oldValue = strtotime($oldValue);
                            }
                            if (is_string($value) && !is_numeric($value)) {
                                $value = strtotime($value);
                            }
                            break;
                    }
                }
            }
            if ($oldValue!= $value) {
                $changes[] = [
                    'field' => $key,
                    'from' => $old->getData($key),
                    'to' => $new->getData($key)
                ];
                $old->setData($key, $value);
            }
        }
        return $changes;
    }

    public function getDeletedIds($epaceEntity, $magentoClass, $epaceIdField, $selectCallback = null, $selectFilter = null)
    {
        /** @var Blackbox_Epace_Helper_Data $helper */
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $helper = $objectManager->create('Blackbox\Epace\Helper\Epace');

        $epaceClass = $helper->getTypeName($epaceEntity);

        /** @var Mage_Core_Model_Resource $resource */
        
        $resource = $objectManager->create('\Magento\Framework\App\ResourceConnection');
        $connection = $resource->getConnection();
//        $orderTable = $resource->getTableName('sales_order');
//        $orderItemTable = $resource->getTableName('sales_order_item');

        /** @var Mage_Core_Model_Resource_Db_Collection_Abstract $magentoCollection */
        // need some follow up
        $magentoCollection = $objectManager->create($magentoClass)->getCollection();

        $select = $connection->select()->from(['main_table' => $magentoCollection->getMainTable()], $epaceIdField);
        if ($selectFilter == 'having') {
            $select->having($epaceIdField . ' IS NOT NULL');
        } else {
            $select->where($epaceIdField . ' IS NOT NULL');
        }

        if (is_callable($selectCallback)) {
            call_user_func($selectCallback, $select, $magentoCollection);
        }

        $importedIds = $connection->fetchCol($select);

        /** @var Blackbox_Epace_Model_Resource_Epace_Collection $collection */
        
        $collection = $objectManager->create('Blackbox\Epace\Model\ResourceModel\\'.$magentoClass.'\\Collection');
        $actualIds = $collection->loadIds();

        return array_diff($importedIds, $actualIds);
    }

    protected function logChanges($message, $changes)
    {
        if (empty($changes)) {
            return;
        }
        $msg = [];
        foreach ($changes as $change) {
            $msg[] = $change['field'] . ': ' . $change['from'] . ' => ' . $change['to'] . '.';
        }
        $this->writeln($message . '. ' . implode(' ', $msg));
    }
}