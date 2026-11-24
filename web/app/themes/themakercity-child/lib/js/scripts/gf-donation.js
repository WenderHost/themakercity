/**
 * Gravity Forms donation form behaviour.
 *
 * Works on any form with the `tmc-donation-form` class, finding its fields by
 * CSS class (see lib/fns/gf-donations.php):
 *
 *   .tmc-donation-frequency  Gift Type radio (One Time / Monthly)
 *   .tmc-donation-presets    preset amount radio, with an `other` choice
 *   .tmc-donation-amount     the Donation Amount number field
 *   .tmc-donation-summary    HTML field with the amount summary and edit link
 *
 * A preset fills the amount; typing an amount selects the matching preset,
 * or Other.
 */
( function ( $ ) {
  'use strict';

  function formatAmount( value ) {
    var number = parseFloat( value );
    if ( isNaN( number ) )
      return '$0';
    var hasCents = Math.round( number * 100 ) % 100 !== 0;
    return '$' + number.toLocaleString( 'en-US', {
      minimumFractionDigits: hasCents ? 2 : 0,
      maximumFractionDigits: 2
    } );
  }

  function init( formId ) {
    var $form = $( '#gform_' + formId );
    if ( ! $form.hasClass( 'tmc-donation-form' ) )
      return;

    var $amount = $form.find( '.tmc-donation-amount input' );
    var $presets = $form.find( '.tmc-donation-presets input[type=radio]' );
    var $frequency = $form.find( '.tmc-donation-frequency input[type=radio]' );

    // GF recalculates totals on `change`; `input` keeps the summary live.
    function setAmount( value ) {
      $amount.val( value ).trigger( 'input' ).trigger( 'change' );
    }

    function syncPresetToAmount() {
      var value = parseFloat( $amount.val() );
      if ( isNaN( value ) ) {
        if ( $presets.filter( ':checked' ).val() !== 'other' )
          $presets.prop( 'checked', false );
        return;
      }
      var $match = $presets.filter( function () {
        return this.value !== 'other' && parseFloat( this.value ) === value;
      } );
      ( $match.length ? $match : $presets.filter( '[value=other]' ) ).prop( 'checked', true );
    }

    function updateSummary() {
      var monthly = $frequency.filter( ':checked' ).val() === 'Monthly Gift';
      $form.find( '.tmc-donation-summary-amount' ).text( formatAmount( $amount.val() ) );
      $form.find( '.tmc-donation-summary-frequency' ).text( monthly ? ' each month' : '' );

      // Page 3: say above the total whether it's charged once or monthly.
      var $total = $form.find( '.tmc-donation-total' );
      var $note = $total.find( '.tmc-donation-frequency-note' );
      if ( ! $note.length )
        $note = $( '<p class="tmc-donation-frequency-note"></p>' ).prependTo( $total );
      $note.text( monthly ? 'Monthly gift, charged every month' : 'One-time gift' );
      $total.toggleClass( 'is-monthly', monthly );
    }

    $presets.off( '.tmcDonation' ).on( 'change.tmcDonation', function () {
      if ( this.value === 'other' ) {
        setAmount( '' );
        $amount.trigger( 'focus' );
      } else {
        setAmount( this.value );
      }
    } );

    $amount.off( '.tmcDonation' ).on( 'input.tmcDonation', function () {
      syncPresetToAmount();
      updateSummary();
    } );

    $frequency.off( '.tmcDonation' ).on( 'change.tmcDonation', updateSummary );

    // "(edit amount)" goes back to page 1, as GF's own Back button does.
    $form.find( '.tmc-donation-edit-amount' ).off( '.tmcDonation' ).on( 'click.tmcDonation', function ( event ) {
      event.preventDefault();
      $( '#gform_target_page_number_' + formId ).val( '1' );
      $form.trigger( 'submit', [ true ] );
    } );

    syncPresetToAmount();
    updateSummary();
  }

  $( document ).on( 'gform_post_render', function ( event, formId ) {
    init( formId );
  } );
} )( jQuery );
