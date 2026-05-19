{**
* 2007-2018 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License 3.0 (AFL-3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* https://opensource.org/licenses/AFL-3.0
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
* @author    PrestaShop SA <contact@prestashop.com>
* @copyright 2007-2018 PrestaShop SA
* @license   https://opensource.org/licenses/AFL-3.0 Academic Free License 3.0 (AFL-3.0)
* International Registered Trademark & Property of PrestaShop SA
*}

<!-- Begin Brevo Form -->
<!-- START - We recommend to place the below code in head tag of your website html  -->

  <link rel="stylesheet" href="https://sibforms.com/forms/end-form/build/sib-styles.css">

  <style>

	
	div#sib-container {
		background: none;
		padding: 0;
		width: 100%;
	}

	.sib-form,
	.sib-form-block {
		padding: 0;
	}
	.sib-input.sib-form-block {
    width: 85%;
	display: inline-block;
	height: 50px;
	float: left;
}
.sib-form-block {
    width: 15%;
	display: inline-block;
	height: 50px;
	float: left;
}
.sib-input.sib-form-block input#EMAIL {
	border: none;
	width: 85%;
	height: 100%;
	overflow: hidden;
	min-width: auto;
	border-left:5px;
	border-right:5px;
	font-family: "Oxygen", sans-serif;
	font-size: 12px;
    height: 43px;
    box-sizing: border-box;
}
.block_newsletter form input[type=text]:focus {
	background: none;
	width: 85%;

}

.sib-form .entry__field {

    border-radius: 5px 0px 0 5px;
	margin: 0;
}

.block_newsletter form button[type=submit] {
    position: relative !important;
    padding: 0;
    max-width: unset !important;
    background: #afcb31 !important;
    height: auto;
    top: 0px;
    left: 0;
	right: auto;
    bottom: auto;
    width: 100%;
    border-radius: 0px 5px 5px 0;
    height: 45px;
    min-height: unset;;
}

.block_newsletter form button[type=submit] i {
	color: #fff;
	font-size: 16px;
}

p.alert.alert-danger.block_newsletter_alert,
label.entry__error.entry__error--primary {
    display: none !important;
}

textarea:focus, input:focus{
    outline: none !important;
	border-color: inherit;
  -webkit-box-shadow: none;
  box-shadow: none;
}

*:focus {
    outline: none !important;
	border-color: inherit;
  -webkit-box-shadow: none;
  box-shadow: none;
}

.sib-input.sib-form-block input#EMAIL {
	outline: none !important;
	border-color: inherit;
  -webkit-box-shadow: none;
  box-shadow: none;
}

.block_newsletter form input[type=text]:focus {
  outline: none;
  text-decoration: underline;
  box-shadow: 0 1px 6px 0 rgba(0, 0, 0, 0.0);
}


  </style>
  <!--  END - We recommend to place the above code in head tag of your website html -->
  <div class="email_subscription block_newsletter" id="blockEmailSubscription_{$hookName}">
	<p>Meld je aan voor onze nieuwsbrief en ontvang 10% korting op je eerste bestelling. Ben altijd als eerste op de hoogte van onze speciale promoties en aanbiedingen.</p>

  <!-- START - We recommend to place the below code where you want the form in your website html  -->
  <div class="sib-form" style="text-align: center; margin-top: 15px;">
	<div id="sib-form-container" class="sib-form-container">
	  <div id="error-message" class="sib-form-message-panel"">
		<div class="sib-form-message-panel__text sib-form-message-panel__text--center">
		  <svg viewBox="0 0 512 512" class="sib-icon sib-notification__icon">
			<path d="M256 40c118.621 0 216 96.075 216 216 0 119.291-96.61 216-216 216-119.244 0-216-96.562-216-216 0-119.203 96.602-216 216-216m0-32C119.043 8 8 119.083 8 256c0 136.997 111.043 248 248 248s248-111.003 248-248C504 119.083 392.957 8 256 8zm-11.49 120h22.979c6.823 0 12.274 5.682 11.99 12.5l-7 168c-.268 6.428-5.556 11.5-11.99 11.5h-8.979c-6.433 0-11.722-5.073-11.99-11.5l-7-168c-.283-6.818 5.167-12.5 11.99-12.5zM256 340c-15.464 0-28 12.536-28 28s12.536 28 28 28 28-12.536 28-28-12.536-28-28-28z" />
		  </svg>
		  <span class="sib-form-message-panel__inner-text">
							Je registratie kon niet worden opgeslagen. Probeer het opnieuw.
						</span>
		</div>
	  </div>
	  <div></div>
	  <div id="success-message" class="sib-form-message-panel">
		<div class="sib-form-message-panel__text sib-form-message-panel__text--center">
		  <svg viewBox="0 0 512 512" class="sib-icon sib-notification__icon">
			<path d="M256 8C119.033 8 8 119.033 8 256s111.033 248 248 248 248-111.033 248-248S392.967 8 256 8zm0 464c-118.664 0-216-96.055-216-216 0-118.663 96.055-216 216-216 118.664 0 216 96.055 216 216 0 118.663-96.055 216-216 216zm141.63-274.961L217.15 376.071c-4.705 4.667-12.303 4.637-16.97-.068l-85.878-86.572c-4.667-4.705-4.637-12.303.068-16.97l8.52-8.451c4.705-4.667 12.303-4.637 16.97.068l68.976 69.533 163.441-162.13c4.705-4.667 12.303-4.637 16.97.068l8.451 8.52c4.668 4.705 4.637 12.303-.068 16.97z" />
		  </svg>
		  <span class="sib-form-message-panel__inner-text">
							Uw registratie is geslaagd.
						</span>
		</div>
	  </div>
	  <div></div>
	  <div id="sib-container" class="sib-container--large sib-container--vertical">
		<form id="sib-form" method="POST" action="https://a1159118.sibforms.com/serve/MUIFAIhn7tj9aWun27KNAiJOp78xz-A18h4kYHomJuE54iVMuo4YpgUHeCjEn71f8jqWhic_vZIzEFtZ1GdocPzN8zXV3PB0wK_HX6x_H4-kdRU26aPa4ISGS5NKbPrYoOfFjODFuvkbD3e5_-OS1A0sNYMTNx5bSm0wyhDLIqCfFH2vtYt5MFDDkniI6MDnXiBgX-VwCkvAkzTg" data-type="subscription">
			<div class="sib-input sib-form-block">
			  <div class="form__entry entry_block">
				<div class="form__label-row ">
 
				  <div class="entry__field">
					<input class="input " type="text" id="EMAIL" name="EMAIL" autocomplete="off" placeholder="E-mailadres" data-required="true" required />
				  </div>
				</div>
  
				<label class="entry__error entry__error--primary" style="font-size:16px; text-align:left; font-family:&quot;Helvetica&quot;, sans-serif; color:#661d1d; background-color:#ffeded; border-radius:3px; border-color:#ff4949;">
				</label>
			  </div>
			</div>
			<div class="sib-form-block" style="">
			  <button class="sib-form-block__button sib-form-block__button-with-loader" form="sib-form" type="submit">
				<svg class="icon clickable__icon progress-indicator__icon sib-hide-loader-icon" viewBox="0 0 512 512">
				  <path d="M460.116 373.846l-20.823-12.022c-5.541-3.199-7.54-10.159-4.663-15.874 30.137-59.886 28.343-131.652-5.386-189.946-33.641-58.394-94.896-95.833-161.827-99.676C261.028 55.961 256 50.751 256 44.352V20.309c0-6.904 5.808-12.337 12.703-11.982 83.556 4.306 160.163 50.864 202.11 123.677 42.063 72.696 44.079 162.316 6.031 236.832-3.14 6.148-10.75 8.461-16.728 5.01z" />
				</svg>
				<i class="icop-paperjet"></i>
			  </button>
			</div>
			<div class="g-recaptcha-v3" data-sitekey="6Ld9bacpAAAAALOSzoj7fZcvrhQX9KOIL-UAs5FV" style="display: none"></div>
  
		  <input type="text" name="email_address_check" value="" class="input--hidden">
		  <input type="hidden" name="locale" value="en">
		</form>
	  </div>
	</div>
  </div>
  <!-- END - We recommend to place the above code where you want the form in your website html  -->
  
</div>
