<?php

namespace Ahmedd\ZohoBooks;

use Ahmedd\ZohoBooks\TokenManager;

class Api
{
    /**
     * @var string
     */
    protected $tokenManager;
    /**
     * @var string
     */
    protected $authToken;
    /**
     * @var string
     */
    protected $email;
    /**
     * @var string
     */
    protected $password;
    /**
     * @var Client
     */
    protected $client;
    /**
     * @var string
     */
    protected $organizationId;

    /**
     * Api constructor.
     *
     * @param string $emailOrToken
     * @param null $password
     */
    public function __construct($authTokenOrManager)
    {

      if ($authTokenOrManager instanceof TokenManager) {
          $this->tokenManager = $authTokenOrManager;
          $this->authToken = $this->tokenManager->getAccessToken();
      } else {
          $this->authToken = $authTokenOrManager;
      }
    }

    /**
     * @return Client
     */
    public function getClient()
    {
        if (null === $this->client) {
            $this->setClient(new Client($this->authToken));
        }

        return $this->client;
    }

    /**
     * @return string|null
     */
    public function getOrganizationId()
    {
        return $this->organizationId;
    }

    /**
     * @param Client $client
     *
     * @return Api
     */
    public function setClient(Client $client)
    {
        $this->client = $client;

        return $this;
    }

    /**
     * @param string|null $organizationId
     *
     * @return Api
     */
    public function setOrganizationId($organizationId)
    {
        $this->organizationId = $organizationId;

        return $this;
    }

    /**
     * @param string|null $organizationId
     *
     * @return \Ahmedd\ZohoBooks\Api\Contacts
     */
    public function contacts($organizationId = null)
    {
        return new Api\Contacts($this->getClient(), $organizationId ?: $this->getOrganizationId());
    }

    /**
     * @param string|null $organizationId
     *
     * @return \Ahmedd\ZohoBooks\Api\Estimates
     */
    public function estimates($organizationId = null)
    {
        return new Api\Estimates($this->getClient(), $organizationId ?: $this->getOrganizationId());
    }

    /**
     * @param string|null $organizationId
     *
     * @return \Ahmedd\ZohoBooks\Api\Bills
     */
    public function bills($organizationId = null)
    {
        return new Api\Bills($this->getClient(), $organizationId ?: $this->getOrganizationId());
    }

    /**
     * @param string|null $organizationId
     *
     * @return \Ahmedd\ZohoBooks\Api\BankAccounts
     */
    public function bankAccounts($organizationId = null)
    {
        return new Api\BankAccounts($this->getClient(), $organizationId ?: $this->getOrganizationId());
    }

    /**
     * @param string|null $organizationId
     *
     * @return \Ahmedd\ZohoBooks\Api\BankRules
     */
    public function bankRules($organizationId = null)
    {
        return new Api\BankRules($this->getClient(), $organizationId ?: $this->getOrganizationId());
    }

    /**
     * @param string|null $organizationId
     *
     * @return \Ahmedd\ZohoBooks\Api\BankTransactions
     */
    public function bankTransactions($organizationId = null)
    {
        return new Api\BankTransactions($this->getClient(), $organizationId ?: $this->getOrganizationId());
    }

    /**
     * @param string|null $organizationId
     *
     * @return \Ahmedd\ZohoBooks\Api\BaseCurrencyAdjustment
     */
    public function baseCurrencyAdjustment($organizationId = null)
    {
        return new Api\BaseCurrencyAdjustment($this->getClient(), $organizationId ?: $this->getOrganizationId());
    }

    /**
     * @param string|null $organizationId
     *
     * @return \Ahmedd\ZohoBooks\Api\ChartOfAccounts
     */
    public function chartOfAccounts($organizationId = null)
    {
        return new Api\ChartOfAccounts($this->getClient(), $organizationId ?: $this->getOrganizationId());
    }

    /**
     * @param string|null $organizationId
     *
     * @return \Ahmedd\ZohoBooks\Api\CreditNotes
     */
    public function creditNotes($organizationId = null)
    {
        return new Api\CreditNotes($this->getClient(), $organizationId ?: $this->getOrganizationId());
    }

    /**
     * @param string|null $organizationId
     *
     * @return \Ahmedd\ZohoBooks\Api\CustomerPayments
     */
    public function customerPayments($organizationId = null)
    {
        return new Api\CustomerPayments($this->getClient(), $organizationId ?: $this->getOrganizationId());
    }

    /**
     * @param string|null $organizationId
     *
     * @return \Ahmedd\ZohoBooks\Api\Expenses
     */
    public function expenses($organizationId = null)
    {
        return new Api\Expenses($this->getClient(), $organizationId ?: $this->getOrganizationId());
    }

    /**
     * @param string|null $organizationId
     *
     * @return \Ahmedd\ZohoBooks\Api\Invoices
     */
    public function invoices($organizationId = null)
    {
        return new Api\Invoices($this->getClient(), $organizationId ?: $this->getOrganizationId());
    }

    /**
     * @param string|null $organizationId
     *
     * @return \Ahmedd\ZohoBooks\Api\Journals
     */
    public function journals($organizationId = null)
    {
        return new Api\Journals($this->getClient(), $organizationId ?: $this->getOrganizationId());
    }

    /**
     * @param string|null $organizationId
     *
     * @return \Ahmedd\ZohoBooks\Api\Projects
     */
    public function projects($organizationId = null)
    {
        return new Api\Projects($this->getClient(), $organizationId ?: $this->getOrganizationId());
    }

    /**
     * @param string|null $organizationId
     *
     * @return \Ahmedd\ZohoBooks\Api\PurchaseOrder
     */
    public function purchaseOrder($organizationId = null)
    {
        return new Api\PurchaseOrder($this->getClient(), $organizationId ?: $this->getOrganizationId());
    }

    /**
     * @param string|null $organizationId
     *
     * @return \Ahmedd\ZohoBooks\Api\RecurringExpenses
     */
    public function recurringExpenses($organizationId = null)
    {
        return new Api\RecurringExpenses($this->getClient(), $organizationId ?: $this->getOrganizationId());
    }

    /**
     * @param string|null $organizationId
     *
     * @return \Ahmedd\ZohoBooks\Api\RecurringInvoices
     */
    public function recurringInvoices($organizationId = null)
    {
        return new Api\RecurringInvoices($this->getClient(), $organizationId ?: $this->getOrganizationId());
    }

    /**
     * @param string|null $organizationId
     *
     * @return \Ahmedd\ZohoBooks\Api\SalesOrder
     */
    public function salesOrder($organizationId = null)
    {
        return new Api\SalesOrder($this->getClient(), $organizationId ?: $this->getOrganizationId());
    }

    /**
     * @param string|null $organizationId
     *
     * @return \Ahmedd\ZohoBooks\Api\Settings
     */
    public function settings($organizationId = null)
    {
        return new Api\Settings($this->getClient(), $organizationId ?: $this->getOrganizationId());
    }

    /**
     * @param string|null $organizationId
     *
     * @return \Ahmedd\ZohoBooks\Api\VendorCredits
     */
    public function vendorCredits($organizationId = null)
    {
        return new Api\VendorCredits($this->getClient(), $organizationId ?: $this->getOrganizationId());
    }

    /**
     * @param string|null $organizationId
     *
     * @return \Ahmedd\ZohoBooks\Api\VendorPayments
     */
    public function vendorPayments($organizationId = null)
    {
        return new Api\VendorPayments($this->getClient(), $organizationId ?: $this->getOrganizationId());
    }

    /**
     * @param string|null $organizationId
     *
     * @return \Ahmedd\ZohoBooks\Api\Items
     */
    public function items($organizationId = null)
    {
        return new Api\Items($this->getClient(), $organizationId ?: $this->getOrganizationId());
    }

    /**
     * @param null $organizationId
     *
     * @return Api\ItemAdjustments
     */
    public function inventoryAdjustments($organizationId = null)
    {
        return new Api\ItemAdjustments($this->getClient(), $organizationId ?: $this->getOrganizationId());
    }
}
