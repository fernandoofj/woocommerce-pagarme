<?php
/**
 * @author      Open Source Team
 * @copyright   2022 Pagar.me (https://pagar.me)
 * @license     https://pagar.me Copyright
 *
 * @link        https://pagar.me
 */

declare( strict_types=1 );

namespace Woocommerce\Pagarme\Model;

use Pagarme\Core\Hub\Services\HubIntegrationService;
use Woocommerce\Pagarme\Core;
use Woocommerce\Pagarme\Model\Config\PagarmeCoreConfigManagement;
use Woocommerce\Pagarme\Model\Config\Source\EnvironmentsTypes;
use Woocommerce\Pagarme\Model\Data\DataObject;
use Woocommerce\Pagarme\Model\Serialize\Serializer\Json;
use Woocommerce\Pagarme\Concrete\WoocommerceCoreSetup as CoreSetup;

defined( 'ABSPATH' ) || exit;

/**
 * Class Config
 * @package Woocommerce\Pagarme\Model\Data
 */
class Config extends DataObject
{
    /** @var string */
    const HUB_SANDBOX_ENVIRONMENT = 'Sandbox';

    /** @var PagarmeCoreConfigManagement */
    private $pagarmeCoreConfigManagement;

    /**
     * @param PagarmeCoreConfigManagement|null $pagarmeCoreConfigManagement
     * @param Json|null $jsonSerialize
     * @param array $data
     */
    public function __construct(
        PagarmeCoreConfigManagement $pagarmeCoreConfigManagement = null,
        Json $jsonSerialize = null,
        array $data = []
    ) {
        $this->pagarmeCoreConfigManagement = $pagarmeCoreConfigManagement ?? new PagarmeCoreConfigManagement;
        parent::__construct($jsonSerialize, $data);
        $this->init();
    }

    /**
     * @return void
     */
    private function init()
    {
        if (is_array($this->getOptions()) || is_object($this->getOptions())) {
            foreach ($this->getOptions() as $key => $value) {
                $this->setData($key, $value);
            }
            add_action(
                'update_option_' . $this->getOptionKey(),
                [ $this, 'updateOption' ],
                10, 3
            );
        }
    }

    /**
     * @return void
     */
    public function updateOption()
    {
        if (array_key_exists($this->getOptionKey(), $_POST)) {
            $values = $_POST[$this->getOptionKey()];
            if ($values && is_array($values)) {
                foreach ($values as $key => $value) {
                    $this->setData($key, sanitize_text_field($value));
                }
            }
            $this->save();
        }
    }

    /**
     * @param Config|null $config
     * @return void
     */
    public function save(Config $config = null)
    {
        if (!$config) {
            $config = $this;
        }
        update_option($this->getOptionKey(), $config->getData());
        $this->pagarmeCoreConfigManagement->update($config);
    }

    /**
     * @return false|mixed|null
     */
    private function getOptions()
    {
        return get_option($this->getOptionKey());
    }

    /**
     * @return string
     */
    public function getOptionKey()
    {
        return Core::tag_name('settings');
    }

    /**
     * @return mixed
     */
    private function getHubAppId()
    {
        return CoreSetup::getHubAppPublicAppKey();
    }

    /**
     * @return bool
     */
    public function getIsSandboxMode()
    {
        return ( $this->getHubEnvironment() === static::HUB_SANDBOX_ENVIRONMENT ||
            strpos(($this->getProductionSecretKey()) ?? '', 'sk_test') !== false ||
            strpos(($this->getProductionPublicKey()) ?? '', 'pk_test') !== false
        );
    }

    /**
     * @return string
     */
    public function getHubUrl(): string
    {
        return ($this->getHubInstallId()) ? $this->getHubViewIntegrationUrl() : $this->getHubIntegrateUrl();
    }

    /**
     * @return string
     */
    private function getHubIntegrateUrl(): string
    {
        return $this->getHubBaseUrl() . $this->getHubParamsUrl();
    }

    /**
     * @return string
     */
    private function getHubBaseUrl()
    {
        return sprintf(
            'https://hub.pagar.me/apps/%s/authorize',
            $this->getHubAppId()
        );
    }

    /**
     * @return string
     */
    private function getHubParamsUrl()
    {
        return sprintf(
            '?redirect=%s?install_token=%s',
            Core::get_hub_url(),
            $this->getHubInstallToken()
        );
    }

    /**
     * @return string
     */
    private function getHubViewIntegrationUrl()
    {
        return sprintf(
            'https://hub.pagar.me/apps/%s/edit/%s',
            $this->getHubAppId(),
            $this->getHubInstallId()
        );
    }

    /**
     * @return string
     */
    private function getHubInstallToken()
    {
        $installSeed = uniqid();
        $hubIntegrationService = new HubIntegrationService();
        $installToken = $hubIntegrationService
            ->startHubIntegration($installSeed);
        return $installToken->getValue();
    }

    /**
     * @return bool
     */
    public function isAccAndMerchSaved() : bool {
        return $this->getMerchantId() && $this->getAccountId();
    }

    /**
     * @return mixed
     */
    public function getDashUrl() {
        if (!$this->isAccAndMerchSaved()) {
            return null;
        }
        return sprintf(
            'https://dash.pagar.me/%s/%s/',
            $this->getMerchantId(),
            $this->getAccountId()
        );
    }

    /**
     * @return string
     */
    public function getPublicKey()
    {
        $publicKey = $this->getData('production_public_key');
        if ($this->getHubEnvironment() === EnvironmentsTypes::SANDBOX_VALUE && $this->getData('sandbox_public_key')) {
            $publicKey = $this->getData('sandbox_public_key');
        }
        return $publicKey;
    }

    /**
     * @return string
     */
    public function getSecretKey()
    {
        $publicKey = $this->getData('production_secret_key');
        if ($this->getHubEnvironment() === EnvironmentsTypes::SANDBOX_VALUE && $this->getData('sandbox_secret_key')) {
            $publicKey = $this->getData('sandbox_secret_key');
        }
        return $publicKey;
    }

    public function getCardOperationForCore()
    {
        return ((int)$this->getCcOperationType() === 2 ? 'auth_and_capture' : 'auth_only');
    }

    /**
     * @return array|mixed
     */
    public function getCcFlags()
    {
        $ccFlags = [];
        if ($value = $this->getData('cc_flags')) {
            $ccFlags = $value;
        }
        return $ccFlags;
    }

    public function getMulticustomers()
    {
        return $this->getData('multicustomers') === 'yes';
    }

    public function getCcAllowSave()
    {
        return $this->getData('cc_allow_save') === 'yes';
    }

    public function getVoucherCardWallet()
    {
        return $this->getData('voucher_card_wallet') === 'yes';
    }

    public function getEnableLogs()
    {
        return $this->getData('enable_logs') === 'yes';
    }

    public function getIsGatewayIntegrationType()
    {
        return $this->getData('is_gateway_integration_type') === 'yes';
    }

    public function getIsVoucherSettingsEnabled()
    {
        if (!$this->getAccountId() || !$this->getIsPaymentEnabled()) {
            return $this->getIsGatewayIntegrationType();
        }
        return $this->getIsPaymentEnabled()['voucher'] || $this->isPaymentGateway()['voucher'];
    }

    public function getIsInstallmentsDefaultConfig()
    {
        return $this->getData('cc_installment_type') === '1';
    }

    public function getAntifraudEnabled()
    {
        return $this->getData('antifraud_enabled') === 'yes';
    }

    public function log()
    {
        return new \WC_Logger();
    }

}
