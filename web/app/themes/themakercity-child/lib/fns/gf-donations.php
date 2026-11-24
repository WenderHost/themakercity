<?php
/**
 * Gravity Forms donation form.
 *
 * A multi-page donation form (amount → contact/billing → payment) built from
 * native Gravity Forms fields and paid through the Gravity Forms Stripe
 * Add-On. Nothing here is tied to a form ID: any form whose CSS class list
 * includes `tmc-donation-form` gets the donation styles and behaviour, and
 * the front end finds its fields by CSS class.
 *
 * Pricing is native GF: two Calculation products read the Donation Amount
 * number field, one for the gift and one for the optional transaction fee
 * (shown only when "cover the fee" is checked), and the Stripe feeds charge
 * the Form Total. To change the fee, edit that product's formula and the
 * checkbox label in the form editor.
 *
 * `wp donation-form install` creates the form and its two Stripe feeds
 * (one-time and monthly, chosen by the Gift Type field).
 */

namespace TheMakerCity\donations;

const FORM_CLASS = 'tmc-donation-form';

/**
 * Whether a form is a donation form.
 *
 * @param array $form The form object.
 *
 * @return bool
 */
function is_donation_form( $form ) {
  $classes = preg_split( '/\s+/', (string) rgar( $form, 'cssClass' ) );
  return in_array( FORM_CLASS, $classes, true );
}

/**
 * Loads the donation styles and script with any donation form.
 *
 * @param array $form    The form being rendered.
 * @param bool  $is_ajax Whether the form uses AJAX.
 */
function enqueue_assets( $form, $is_ajax ) {
  if ( ! is_donation_form( $form ) )
    return;

  $css_dir = ( 'development' != MAKR_ENV )? 'css-dist' : 'css' ;
  $css     = 'lib/' . $css_dir . '/gf-donation.css';
  $js      = 'lib/js/scripts/gf-donation.js';

  wp_enqueue_style( 'tmc-gf-donation', MAKR_STYLESHEET_DIR_URI . $css, [], filemtime( MAKR_STYLESHEET_DIR . $css ) );
  wp_enqueue_script( 'tmc-gf-donation', MAKR_STYLESHEET_DIR_URI . $js, [ 'jquery', 'gform_gravityforms' ], filemtime( MAKR_STYLESHEET_DIR . $js ), true );
}
add_action( 'gform_enqueue_scripts', __NAMESPACE__ . '\\enqueue_assets', 10, 2 );

/**
 * The form definition `wp donation-form install` creates.
 *
 * Field IDs are fixed here only so the formulas and feeds below can refer to
 * them; the front end doesn't depend on them.
 *
 * @param string $title The form title.
 *
 * @return array
 */
function form_definition( $title ) {
  $amount_choices = [];
  foreach ( [ 25, 50, 100, 500, 1000, 1500, 2000 ] as $amount ) {
    $amount_choices[] = [ 'text' => '$' . number_format( $amount ), 'value' => (string) $amount, 'isSelected' => false ];
  }
  $amount_choices[] = [ 'text' => 'Other', 'value' => 'other', 'isSelected' => false ];

  $fee_percent = 3;

  $fields = [
    // Page 1: amount and frequency.
    [
      'type'       => 'radio',
      'id'         => 1,
      'label'      => 'Gift Type',
      'isRequired' => true,
      'cssClass'   => 'tmc-donation-frequency',
      'labelPlacement' => 'hidden_label',
      'choices'    => [
        [ 'text' => 'One Time Gift', 'value' => 'One Time Gift', 'isSelected' => true ],
        [ 'text' => 'Monthly Gift', 'value' => 'Monthly Gift', 'isSelected' => false ],
      ],
    ],
    [
      'type'     => 'radio',
      'id'       => 2,
      'label'    => 'Choose an Amount',
      'cssClass' => 'tmc-donation-presets',
      'labelPlacement' => 'hidden_label',
      'choices'  => $amount_choices,
    ],
    [
      'type'        => 'textarea',
      'id'          => 3,
      'label'       => 'Note',
      'placeholder' => 'Notes, In Honor Of, In Memory Of, etc.',
      'size'        => 'small',
    ],
    [
      'type'         => 'number',
      'id'           => 4,
      'label'        => 'Donation Amount',
      'isRequired'   => true,
      'numberFormat' => 'decimal_dot',
      'rangeMin'     => '1',
      'cssClass'     => 'tmc-donation-amount',
      'placeholder'  => '0',
      'errorMessage' => 'Please enter a donation amount of at least $1.',
    ],
    [
      'type'     => 'page',
      'id'       => 5,
      'nextButton'     => [ 'type' => 'text', 'text' => 'Continue' ],
      'previousButton' => [ 'type' => 'text', 'text' => 'Back' ],
    ],

    // Page 2: contact and billing.
    [
      'type'  => 'section',
      'id'    => 6,
      'label' => 'Contact Information',
      'cssClass' => 'tmc-donation-heading',
    ],
    [
      'type'     => 'checkbox',
      'id'       => 7,
      'label'    => 'Donate as a Business',
      'labelPlacement' => 'hidden_label',
      'cssClass' => 'tmc-donation-business',
      'choices'  => [ [ 'text' => 'Donate as a Business', 'value' => 'Yes', 'isSelected' => false ] ],
      'inputs'   => [ [ 'id' => '7.1', 'label' => 'Donate as a Business', 'name' => '' ] ],
    ],
    [
      'type'       => 'text',
      'id'         => 8,
      'label'      => 'Business Name',
      'isRequired' => true,
      'conditionalLogic' => [
        'actionType' => 'show',
        'logicType'  => 'all',
        'rules'      => [ [ 'fieldId' => '7', 'operator' => 'is', 'value' => 'Yes' ] ],
      ],
    ],
    [
      'type'       => 'name',
      'id'         => 9,
      'label'      => 'Name',
      'isRequired' => true,
      'nameFormat' => 'advanced',
      'inputs'     => [
        [ 'id' => '9.2', 'label' => 'Prefix', 'name' => '', 'isHidden' => true ],
        [ 'id' => '9.3', 'label' => 'First Name', 'name' => '', 'customLabel' => 'First Name' ],
        [ 'id' => '9.4', 'label' => 'Middle', 'name' => '', 'isHidden' => true ],
        [ 'id' => '9.6', 'label' => 'Last Name', 'name' => '', 'customLabel' => 'Last Name' ],
        [ 'id' => '9.8', 'label' => 'Suffix', 'name' => '', 'isHidden' => true ],
      ],
    ],
    [
      'type'       => 'email',
      'id'         => 10,
      'label'      => 'Email Address',
      'isRequired' => true,
      'layoutGridColumnSpan' => 6,
    ],
    [
      'type'        => 'phone',
      'id'          => 11,
      'label'       => 'Phone Number',
      'isRequired'  => true,
      'phoneFormat' => 'standard',
      'layoutGridColumnSpan' => 6,
    ],
    [
      'type'     => 'checkbox',
      'id'       => 12,
      'label'    => "It's OK to send me updates via:",
      'cssClass' => 'tmc-donation-optin',
      'choices'  => [
        [ 'text' => 'Email', 'value' => 'Email', 'isSelected' => false ],
        [ 'text' => 'Text Messaging', 'value' => 'Text Messaging', 'isSelected' => false ],
      ],
      'inputs'   => [
        [ 'id' => '12.1', 'label' => 'Email', 'name' => '' ],
        [ 'id' => '12.2', 'label' => 'Text Messaging', 'name' => '' ],
      ],
    ],
    [
      'type'  => 'section',
      'id'    => 13,
      'label' => 'Billing Information',
      'cssClass' => 'tmc-donation-heading',
    ],
    [
      'type'           => 'address',
      'id'             => 14,
      'label'          => 'Address',
      'labelPlacement' => 'hidden_label',
      'isRequired'     => true,
      'addressType'    => 'international',
      'defaultCountry' => 'United States',
      'inputs'         => [
        [ 'id' => '14.1', 'label' => 'Address', 'name' => '', 'customLabel' => 'Address' ],
        [ 'id' => '14.2', 'label' => 'Address Line 2', 'name' => '' ],
        [ 'id' => '14.3', 'label' => 'City', 'name' => '' ],
        [ 'id' => '14.4', 'label' => 'State', 'name' => '', 'customLabel' => 'State' ],
        [ 'id' => '14.5', 'label' => 'Zip Code', 'name' => '', 'customLabel' => 'Zip Code' ],
        [ 'id' => '14.6', 'label' => 'Country', 'name' => '' ],
      ],
    ],
    [
      'type'    => 'html',
      'id'      => 15,
      'label'   => 'Donation Summary',
      'cssClass' => 'tmc-donation-summary',
      'content' => '<p>You are giving a donation of <strong class="tmc-donation-summary-amount"></strong> USD<span class="tmc-donation-summary-frequency"></span>. <a href="#" class="tmc-donation-edit-amount">(edit amount)</a></p>',
    ],
    [
      'type'     => 'page',
      'id'       => 16,
      'nextButton'     => [ 'type' => 'text', 'text' => 'Continue' ],
      'previousButton' => [ 'type' => 'text', 'text' => 'Back' ],
    ],

    // Page 3: payment.
    [
      'type'  => 'section',
      'id'    => 17,
      'label' => 'Payment Details',
      'cssClass' => 'tmc-donation-heading',
    ],
    [
      'type'       => 'stripe_creditcard',
      'id'         => 18,
      'label'      => 'Payment',
      'labelPlacement' => 'hidden_label',
      'isRequired' => true,
      'enableMultiplePaymentMethods' => true,
      'linkEmailFieldId' => '10',
      'inputs'     => [
        [ 'id' => '18.1', 'label' => 'Card Details', 'name' => '' ],
        [ 'id' => '18.4', 'label' => 'Card Type', 'name' => '' ],
        [ 'id' => '18.5', 'label' => 'Cardholder Name', 'name' => '', 'isHidden' => true ],
      ],
    ],
    [
      'type'        => 'checkbox',
      'id'          => 19,
      'label'       => 'Cover Transaction Costs',
      'labelPlacement' => 'hidden_label',
      'cssClass'    => 'tmc-donation-fee',
      'description' => 'Covering our card processing fee means more of your gift goes to Knoxville makers.',
      'choices'     => [ [ 'text' => "Add {$fee_percent}% to your donation to cover our transaction costs?", 'value' => 'Yes', 'isSelected' => false ] ],
      'inputs'      => [ [ 'id' => '19.1', 'label' => "Add {$fee_percent}% to your donation to cover our transaction costs?", 'name' => '' ] ],
    ],
    [
      'type'               => 'product',
      'inputType'          => 'calculation',
      'id'                 => 20,
      'label'              => 'Donation',
      'cssClass'           => 'tmc-donation-product',
      'enableCalculation'  => true,
      'calculationFormula' => '{Donation Amount:4}',
      'calculationRounding' => '2',
      'disableQuantity'    => true,
      'inputs'             => [
        [ 'id' => '20.1', 'label' => 'Name', 'name' => '' ],
        [ 'id' => '20.2', 'label' => 'Price', 'name' => '' ],
        [ 'id' => '20.3', 'label' => 'Quantity', 'name' => '' ],
      ],
    ],
    [
      'type'               => 'product',
      'inputType'          => 'calculation',
      'id'                 => 21,
      'label'              => 'Transaction Fee',
      'cssClass'           => 'tmc-donation-product',
      'enableCalculation'  => true,
      'calculationFormula' => '{Donation Amount:4} * ' . ( $fee_percent / 100 ),
      'calculationRounding' => '2',
      'disableQuantity'    => true,
      'inputs'             => [
        [ 'id' => '21.1', 'label' => 'Name', 'name' => '' ],
        [ 'id' => '21.2', 'label' => 'Price', 'name' => '' ],
        [ 'id' => '21.3', 'label' => 'Quantity', 'name' => '' ],
      ],
      'conditionalLogic' => [
        'actionType' => 'show',
        'logicType'  => 'all',
        'rules'      => [ [ 'fieldId' => '19', 'operator' => 'is', 'value' => 'Yes' ] ],
      ],
    ],
    [
      'type'     => 'total',
      'id'       => 22,
      'label'    => 'Total',
      'labelPlacement' => 'hidden_label',
      'cssClass' => 'tmc-donation-total',
    ],
  ];

  // A page break starts the page it belongs to.
  $page = 1;
  foreach ( $fields as &$field ) {
    if ( 'page' === $field['type'] )
      $page++;
    $field['formId']     = 0;
    $field['pageNumber'] = $page;
  }
  unset( $field );

  return [
    'title'          => $title,
    'description'    => '',
    'cssClass'       => FORM_CLASS,
    'labelPlacement' => 'top_label',
    'descriptionPlacement' => 'below',
    'button'         => [ 'type' => 'text', 'text' => 'Donate' ],
    'lastPageButton' => [ 'type' => 'text', 'text' => 'Back' ],
    'enableHoneypot' => true,
    'pagination'     => [
      'type'  => 'steps',
      'pages' => [ 'Amount', 'Contact', 'Payment' ],
      'display_progressbar_on_confirmation' => false,
    ],
    'firstPageCssClass' => '',
    'fields'         => $fields,
    'confirmations'  => [
      '5f0b8f0d1a2b3' => [
        'id'        => '5f0b8f0d1a2b3',
        'name'      => 'Default Confirmation',
        'isDefault' => true,
        'type'      => 'message',
        'message'   => '<h3>Thank you, {Name (First):9.3}!</h3><p>Your donation of {Total:22} was received. A receipt is on its way to {Email Address:10}.</p>',
      ],
    ],
    'notifications'  => [
      '5f0b8f0d1a2b4' => [
        'id'      => '5f0b8f0d1a2b4',
        'name'    => 'Admin Notification',
        'isActive' => true,
        'event'   => 'form_submission',
        'to'      => '{admin_email}',
        'toType'  => 'email',
        'subject' => 'New donation: {Total:22} ({Gift Type:1}) from {Name:9}',
        'message' => '{all_fields}',
        'from'    => '{admin_email}',
        'replyTo' => '{Email Address:10}',
      ],
    ],
  ];
}

/**
 * The two Stripe feeds: one-time and monthly, chosen by the Gift Type field.
 *
 * @return array[] Feed name => feed meta.
 */
function feed_definitions() {
  $condition = function( $gift_type ) {
    return [
      'feed_condition_conditional_logic'        => '1',
      'feed_condition_conditional_logic_object' => [
        'conditionalLogic' => [
          'actionType' => 'show',
          'logicType'  => 'all',
          'rules'      => [ [ 'fieldId' => '1', 'operator' => 'is', 'value' => $gift_type ] ],
        ],
      ],
    ];
  };

  $shared = [
    'customerInformation_email'       => '10',
    'customerInformation_name'        => '9',
    'billingInformation_address_line1'   => '14.1',
    'billingInformation_address_line2'   => '14.2',
    'billingInformation_address_city'    => '14.3',
    'billingInformation_address_state'   => '14.4',
    'billingInformation_address_zip'     => '14.5',
    'billingInformation_address_country' => '14.6',
    'metaData' => [
      [ 'key' => 'note', 'custom_key' => '', 'value' => '3' ],
      [ 'key' => 'phone', 'custom_key' => '', 'value' => '11' ],
      [ 'key' => 'business_name', 'custom_key' => '', 'value' => '8' ],
    ],
  ];

  return [
    'One-time donation' => array_merge( $shared, $condition( 'One Time Gift' ), [
      'feedName'        => 'One-time donation',
      'transactionType' => 'product',
      'paymentAmount'   => 'form_total',
      'receipt_field'   => '10',
    ] ),
    'Monthly donation' => array_merge( $shared, $condition( 'Monthly Gift' ), [
      'feedName'            => 'Monthly donation',
      'transactionType'     => 'subscription',
      'recurringAmount'     => 'form_total',
      'subscription_name'   => 'Monthly donation to The Maker City',
      'billingCycle_length' => '1',
      'billingCycle_unit'   => 'month',
      'setupFee_enabled'    => '0',
      'trial_enabled'       => '0',
    ] ),
  ];
}

if ( defined( 'WP_CLI' ) && \WP_CLI ) {

  /**
   * Sets up the Gravity Forms donation form.
   */
  class Donation_Form_Command {

    /**
     * Creates the donation form and its Stripe feeds.
     *
     * Each run creates a new form; it never changes an existing one.
     *
     * ## OPTIONS
     *
     * [--title=<title>]
     * : The form title.
     * ---
     * default: Donate Online
     * ---
     *
     * ## EXAMPLES
     *
     *     wp donation-form install
     *
     * @when after_wp_load
     */
    public function install( $args, $assoc_args ) {
      if ( ! class_exists( 'GFAPI' ) || ! function_exists( 'gf_stripe' ) )
        \WP_CLI::error( 'Gravity Forms and the Gravity Forms Stripe Add-On must both be active.' );

      $form_id = \GFAPI::add_form( form_definition( $assoc_args['title'] ) );
      if ( is_wp_error( $form_id ) )
        \WP_CLI::error( $form_id );

      foreach ( feed_definitions() as $name => $meta ) {
        $feed_id = \GFAPI::add_feed( $form_id, $meta, 'gravityformsstripe' );
        if ( is_wp_error( $feed_id ) )
          \WP_CLI::error( "Form {$form_id} was created, but the '{$name}' feed failed: " . $feed_id->get_error_message() );
        \WP_CLI::log( "Added Stripe feed '{$name}' (ID {$feed_id})." );
      }

      \WP_CLI::success( "Created donation form {$form_id}. Embed it with [gravityform id=\"{$form_id}\" title=\"false\" ajax=\"true\"]" );
    }
  }

  \WP_CLI::add_command( 'donation-form', __NAMESPACE__ . '\\Donation_Form_Command' );
}
