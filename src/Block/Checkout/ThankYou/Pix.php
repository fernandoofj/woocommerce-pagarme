<?php
/**
 * @author      Open Source Team
 * @copyright   2022 Pagar.me (https://pagar.me)
 * @license     https://pagar.me Copyright
 *
 * @link        https://pagar.me
 */

declare( strict_types=1 );

namespace Woocommerce\Pagarme\Block\Checkout\ThankYou;

use Woocommerce\Pagarme\Block\Checkout\ThankYou;

defined( 'ABSPATH' ) || exit;

/**
 * Class Pix
 * @package Woocommerce\Pagarme\Block\Checkout\ThankYou
 */
class Pix extends ThankYou
{
    /**
     * @var string
     */
    protected $_template = 'templates/checkout/thankyou/pix';

    /**
     * @return string|null
     */
    public function getQrCodeUrl()
    {
        if ($response = $this->getResponseData()) {
            $charges = $response->charges;
            $charge = array_shift($charges);
            $transaction = array_shift($charge->transactions);
            return $transaction->postData->qr_code_url;
        }
        return null;
    }

    /**
     * @return string|null
     */
    public function getRawQrCode()
    {
        if ($response = $this->getResponseData()) {
            $charges = $response->charges;
            $charge = array_shift($charges);
            $transaction = array_shift($charge->transactions);
            return $transaction->postData->qr_code;
        }
        return null;
    }

    /**
     * @return array
     */
    public function getInstructions()
    {
        return [
            '1. Point your phone at this screen to capture the code.',
            '2. Open your payments app.',
            '3. Confirm the information and complete the payment on the app.',
            '4. We will send you a purchase confirmation.'
        ];
    }
}
